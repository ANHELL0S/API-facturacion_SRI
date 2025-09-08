<?php

namespace App\Services;

use stdClass;

class SriAutorizacion
{

    protected $url;
    protected $clave_acceso;
    protected $recepcion;
    protected $autorizacion;
    protected $ambiente;
    protected $xml;

    public function __construct($ambiente = 'prueba')
    {
        $this->ambiente = $ambiente;
        $this->recepcion = config('signing.recepcion.' . $this->ambiente);
        $this->autorizacion = config('signing.autorizacion.' . $this->ambiente);
    }

    public static function autorizacionComprobante($xml, $claveAcceso, $ambiente)
    {
        $autorizacion = new static($ambiente);
        $autorizacion->clave_acceso = $claveAcceso;
        $autorizacion->xml = $xml;
        $recepcion = $autorizacion->recepcion($xml, $claveAcceso);
        sleep(3);
        return $autorizacion->validacion($recepcion);
    }

    public static function setRecepcion($xml, $claveAcceso, $ambiente)
    {
        $recep = new static($ambiente);
        $recep->clave_acceso = $claveAcceso;
        $recep->xml = $xml;
        $recepcion = $recep->recepcion($xml, $claveAcceso);
        switch ($recepcion->RespuestaRecepcionComprobante->Estado) {
            case 'RECIBIDA':
                return $recep->recibida($recepcion);
            case 'DEVUELTA':
                return $recep->devuelta($recepcion);
            default:
                return $recep->devuelta($recepcion);
        }
    }

    public static function autorizeVoucher($claveAcceso, $ambiente)
    {
        $autorizacion = new static($ambiente);
        $autorizacion->clave_acceso = $claveAcceso;
        return $autorizacion->autorizacion();
    }

    public function recibida($response)
    {
        $data = new stdClass();
        $data->method = 'RECEPCION';
        $data->status = $response->RespuestaRecepcionComprobante->estado;
        $data->response = $response;
        $data->clave_acceso = $this->clave_acceso;
        $data->errors = $this->errores($response->RespuestaRecepcionComprobante->comprobantes->comprobante->mensajes ?? []);
        return $data;
    }

    public function recepcion($xml, $claveAcceso)
    {
        try {
            $ws = new \SoapClient($this->recepcion, [
                'trace' => 1,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'user_agent' => 'MI Cliente SOAP',
                'connection_timeout' => 180,
                'default_socket_timeout' => 180
            ]);
            $params = new stdClass();
            $params->xml = $xml;
            return $ws->validarComprobante($params);
        } catch (\SoapFault $e) {
            echo "SOAP Fault: (faultcode: {$e->faultcode}, faultstring: {$e->faultstring})";
            echo "Last Request Headers: " . $ws->__getLastRequestHeaders();
            echo "Last Request: " . $ws->__getLastRequest();
            echo "Last Response Headers: " . $ws->__getLastResponseHeaders();
            echo "Last Response: " . $ws->__getLastResponse();
        }
    }

    public function validacion($response)
    {
        switch ($response->RespuestaRecepcionComprobante->estado ?? null) {
            case 'RECIBIDA':
                $data = new stdClass();
                $data->status = $response->RespuestaRecepcionComprobante->estado;
                $data->response = $response;
                $data->errors = $this->errores([]);
                return $this->autorizacion();
            case 'DEVUELTA':
                return $this->devuelta($response);
            default:
                return $this->devuelta($response);
        }
    }

    public function autorizacion()
    {
        $ws = new \SoapClient($this->autorizacion, ['trace' => 1, 'cache_wsdl' => WSDL_CACHE_NONE, 'user_agent' => 'MI Cliente SOAP']);
        $params = array(
            'claveAccesoComprobante' => $this->clave_acceso
        );
        $autorizado = $ws->autorizacionComprobante($params);
        return $this->autorizado($autorizado);
    }

    public function autorizando($response, $repeat = 0)
    {
        switch ($response->RespuestaAutorizacionComprobante->autorizaciones->autorizacion->estado ?? '') {
            case 'AUTORIZADO':
                return $this->autorizado($response);
            case 'NO AUTORIZADO':
                return $this->no_autorizado($response);
            case 'EN PROCESO':
                if ($repeat <= 3) {
                    return $this->autorizando($response, $repeat++);
                }
                return $this->en_proceso($response);
            default:
                return $this->no_autorizado($response);
        }
    }

    public function no_autorizado($response)
    {
        $data = new stdClass();
        $data->code = 200;
        $data->method = 'AUTORIZACION';
        $data->status = $response->RespuestaAutorizacionComprobante->autorizaciones->autorizacion->estado ?? '';
        $data->clave_acceso = $this->clave_acceso;
        $data->errors = $this->errores($response->RespuestaAutorizacionComprobante->autorizaciones->autorizacion->mensajes ?? []);
        return $data;
    }

    // public function devuelta($response)
    // {
    //     $data = new stdClass();
    //     $data->code = 200;
    //     $data->method = 'RECEPCION';
    //     $data->status = $response->RespuestaRecepcionComprobante->estado;
    //     $data->clave_acceso = $this->clave_acceso;
    //     $data->response = $response;
    //     $data->errors = $this->errores($response->RespuestaRecepcionComprobante->comprobantes->comprobante->mensajes);
    //     return $data;
    // }


    public function devuelta($response)
    {
        $data = new stdClass();
        $data->code = 200;
        $data->method = 'RECEPCION';

        if (isset($response->RespuestaRecepcionComprobante) && property_exists($response->RespuestaRecepcionComprobante, 'estado')) {
            $data->status = $response->RespuestaRecepcionComprobante->estado;
        } else {
            $data->status = 'UNKNOWN'; // or handle the error as needed
        }

        $data->clave_acceso = $this->clave_acceso;
        $data->response = $response;
        $data->errors = $this->errores($response->RespuestaRecepcionComprobante->comprobantes->comprobante->mensajes);
        return $data;
    }

    public function en_proceso($response)
    {
        $data = new stdClass();
        $data->code = 200;
        $data->method = 'EN PROCESO';
        $data->status = $response->RespuestaRecepcionComprobante->estado;
        $data->clave_acceso = $this->clave_acceso;
        $data->response = $response;
        $data->errors = $this->errores($response->RespuestaRecepcionComprobante->comprobantes->comprobante->mensajes);
        return $data;
    }

    public function autorizado($response)
    {
        $data = new stdClass();
        $data->code = 200;
        $data->method = 'AUTORIZACION';

        $autorizacion = $response->RespuestaAutorizacionComprobante->autorizaciones->autorizacion;

        $data->status = isset($autorizacion->estado) ? $autorizacion->estado : null;
        $data->fecha_autorizacion = isset($autorizacion->fechaAutorizacion) ? $autorizacion->fechaAutorizacion : null;
        $data->numero_autorizacion = isset($autorizacion->numeroAutorizacion) ? $autorizacion->numeroAutorizacion : null;
        $data->ambiente = isset($autorizacion->ambiente) ? $autorizacion->ambiente : null;
        $data->errors = isset($autorizacion->mensajes) ? $this->errores($autorizacion->mensajes) : null;
        $data->response = $autorizacion;

        return $data;
    }

    public function errores($mensajes)
    {
        $message = collect();
        foreach ($mensajes as $key => $mensaje) {
            $message->push($mensaje);
        }
        return $message->toArray();
    }

    public function seeAccesKey($access_key, $ambiente = 'prueba')
    {
        $autorizado = new static($ambiente);
        $autorizado->clave_acceso = $access_key;
        return $autorizado->autorizacion();
    }
}
