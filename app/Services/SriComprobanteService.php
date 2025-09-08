<?php

namespace App\Services;

use App\Exceptions\SriException;
use SoapClient;
use SoapFault;
use Illuminate\Support\Facades\Log;


class SriComprobanteService
{
    // URLs para pruebas y producción
    private const RECEPCION_PRUEBAS = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
    private const RECEPCION_PRODUCCION = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
    private const AUTORIZACION_PRUEBAS = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
    private const AUTORIZACION_PRODUCCION = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';

    /**
     * Verifica la disponibilidad del servicio SRI.
     * @param string $wsdl
     * @return bool
     */
    private function checkSriDisponible(string $wsdl)
    {
        try {
            // Intentar una conexión simple al WSDL con un timeout corto
            $client = new SoapClient($wsdl, [
                'connection_timeout' => 5, // Timeout de 5 segundos
                'exceptions' => true,
            ]);

            // Si la conexión al WSDL es exitosa, asumimos que el servicio está disponible
            return true;
        } catch (SoapFault $e) {
            Log::error("🚫 SRI no disponible (WSDL: {$wsdl}): {$e->getMessage()}");
            return false;
        } catch (\Exception $e) {
            Log::error("🚫 Error al verificar disponibilidad del SRI (WSDL: {$wsdl}): {$e->getMessage()}");
            return false;
        }
    }


    /**
     * Envía un comprobante al SRI para su recepción.
     * @param string $xmlString
     * @return array
     * @throws SriException
     */
    public function enviarComprobanteRecepcion(string $xmlString)
    {
        Log::info('⏳ Enviando comprobante a la recepción');

        // Detectar el ambiente desde el XML
        $ambiente = $this->leerAmbienteDesdeXml($xmlString);

        // Configurar la URL según el ambiente
        $wsdl = ($ambiente == '1') ? self::RECEPCION_PRUEBAS : self::RECEPCION_PRODUCCION;

        // Verificar la disponibilidad del servicio SRI
        if (!$this->checkSriDisponible($wsdl)) {
            throw new SriException('0', 'El servicio SRI no está disponible.');
        }

        try {
            // Cliente SOAP
            $client = new SoapClient($wsdl);

            // Parámetros para el servicio SOAP
            $params = (object) ['xml' => $xmlString];
            $result = $client->validarComprobante($params);

            $estado = $result->RespuestaRecepcionComprobante->estado ?? null;

            if ($estado !== 'RECIBIDA') {
                // Extraer el primer mensaje de error del SRI
                $mensaje = $result->RespuestaRecepcionComprobante->comprobantes->comprobante->mensajes->mensaje ?? null;

                $codigo = $mensaje->identificador ?? '0';
                $descripcion = $mensaje->mensaje ?? 'Error en recepción';
                $informacionAdicional = $mensaje->informacionAdicional ?? null;

                throw new SriException($codigo, $descripcion, [
                    'info_adicional' => $informacionAdicional,
                    'estado_sri' => $estado,
                ]);
            }

            return [
                'success' => true,
                'response' => $result
            ];
        } catch (SoapFault $e) {
            throw new SriException('0', 'Error de conexión con el SRI: ' . $e->getMessage());
        } catch (SriException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new SriException('0', 'Error inesperado en recepción: ' . $e->getMessage());
        }
    }


    /**
     * Envía un comprobante al SRI para autorización.
     * @param string $claveAcceso
     * @param string $ambiente
     * @return array
     * @throws SriException
     */
    public function enviarComprobanteAutorizacion(string $claveAcceso, string $ambiente = '1')
    {
        Log::info('⏳ Enviando comprobante para autorización');
        $wsdl = $ambiente === '1' ? self::AUTORIZACION_PRUEBAS : self::AUTORIZACION_PRODUCCION;

        // Verificar disponibilidad del SRI
        if (!$this->checkSriDisponible($wsdl)) {
            throw new SriException('0', 'El servicio del SRI no está disponible en este momento.');
        }

        try {
            $client = new SoapClient($wsdl);

            $params = (object) ['claveAccesoComprobante' => $claveAcceso];

            $maxIntentos = 5;
            $intentos = 0;

            while ($intentos < $maxIntentos) {
                try {
                    $intentos++;
                    $result = $client->autorizacionComprobante($params);

                    Log::info("🔁 Intento {$intentos} autorización: " . json_encode($result));

                    $autorizaciones = $result->RespuestaAutorizacionComprobante->autorizaciones->autorizacion ?? null;

                    if ($autorizaciones) {
                        $autorizacion = is_array($autorizaciones) ? $autorizaciones[0] : $autorizaciones;

                        if ($autorizacion->estado === 'AUTORIZADO') {
                            return [
                                'success' => true,
                                'autorizacion' => $autorizacion,
                                'mensajes' => $autorizacion->mensajes ?? null
                            ];
                        }

                        $mensaje = $autorizacion->mensajes->mensaje ?? null;
                        $codigo = $mensaje->identificador ?? '0';
                        $descripcion = $mensaje->mensaje ?? 'Comprobante no autorizado';
                        $infoAdicional = $mensaje->informacionAdicional ?? null;

                        throw new SriException($codigo, $descripcion, [
                            'info_adicional' => $infoAdicional,
                            'estado_sri' => $autorizacion->estado,
                        ]);
                    }

                    sleep(1); // Espera antes del siguiente intento
                } catch (SoapFault $e) {
                    throw new SriException('0', 'Error de conexión con el SRI: ' . $e->getMessage());
                } catch (\Exception $e) {
                    if ($intentos >= $maxIntentos) {
                        throw new SriException('0', 'Error inesperado en autorización: ' . $e->getMessage());
                    }
                    sleep(1);
                }
            }

            throw new SriException('0', 'No se recibió una respuesta de autorización válida del SRI después de varios intentos.');
        } catch (SoapFault $e) {
            throw new SriException('0', 'Error de conexión con el SRI: ' . $e->getMessage());
        }
    }


    /**
     * Envía un comprobante al SRI y lo autoriza.
     * @param string $xmlString
     * @param string $claveAcceso
     * @return array
     * @throws SriException
     */
    public function enviarYAutorizarComprobante(string $xmlString, string $claveAcceso): array
    {
        try {
            // 👉 Enviar a recepción
            $recepcion = $this->enviarComprobanteRecepcion($xmlString);

            if (!$recepcion['success']) {
                $estado = $recepcion['estado'] ?? null;
                $mensajes = $recepcion['mensajes'] ?? null;

                $codigo = '0';
                $mensaje = 'El comprobante no fue recibido';

                // Intentar extraer código del primer mensaje de error del SRI
                if (is_object($mensajes)) {
                    $msg = is_array($mensajes) ? $mensajes[0] : $mensajes->mensaje ?? null;

                    if ($msg) {
                        $codigo = $msg->identificador ?? '0';
                        $mensaje = $msg->mensaje ?? $mensaje;
                    }
                }

                throw new SriException($codigo, $mensaje, [
                    'estado' => $estado,
                    'mensajes' => $mensajes
                ]);
            }

            // Pausa para dar tiempo al SRI a procesar
            sleep(2);

            // 👉 Autorización
            $ambiente = $this->leerAmbienteDesdeXml($xmlString);
            return $this->enviarComprobanteAutorizacion($claveAcceso, $ambiente);
        } catch (SriException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new SriException('0', 'Error inesperado al enviar y autorizar comprobante: ' . $e->getMessage());
        }
    }


    /**
     * Consulta el XML autorizado desde el SRI.
     *
     * @param string $claveAcceso Clave de acceso del comprobante
     * @param string $ambiente Ambiente (1: pruebas, 2: producción)
     * @return string XML autorizado
     * @throws SriException
     */
    public function consultarXmlAutorizado(string $claveAcceso, string $ambiente = '1'): string
    {
        Log::info("⏳ Consultando XML autorizado para clave de acceso: {$claveAcceso}");

        $wsdl = ($ambiente === '1') ? self::AUTORIZACION_PRUEBAS : self::AUTORIZACION_PRODUCCION;

        // Verificar disponibilidad del SRI
        if (!$this->checkSriDisponible($wsdl)) {
            throw new SriException('0', 'El servicio del SRI no está disponible en este momento.');
        }

        try {
            $client = new SoapClient($wsdl);
            $params = (object) ['claveAccesoComprobante' => $claveAcceso];

            $result = $client->autorizacionComprobante($params);

            $autorizaciones = $result->RespuestaAutorizacionComprobante->autorizaciones->autorizacion ?? null;

            if ($autorizaciones) {
                $autorizacion = is_array($autorizaciones) ? $autorizaciones[0] : $autorizaciones;

                if ($autorizacion->estado === 'AUTORIZADO') {
                    return $autorizacion->comprobante; // Devolver el XML autorizado
                }

                $mensaje = $autorizacion->mensajes->mensaje ?? null;
                $codigo = $mensaje->identificador ?? '0';
                $descripcion = $mensaje->mensaje ?? 'Comprobante no autorizado';
                $infoAdicional = $mensaje->informacionAdicional ?? null;

                throw new SriException($codigo, $descripcion, [
                    'info_adicional' => $infoAdicional,
                    'estado_sri' => $autorizacion->estado,
                ]);
            }

            throw new SriException('0', 'No se encontró el comprobante en el SRI.');
        } catch (SoapFault $e) {
            throw new SriException('0', 'Error de conexión con el SRI: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new SriException('0', 'Error inesperado al consultar el XML: ' . $e->getMessage());
        }
    }


    /**
     * Lee el ambiente desde el XML.
     * @param string $xmlString
     * @return string
     * @throws \Exception
     */
    private function leerAmbienteDesdeXml(string $xmlString): string
    {
        $xml = simplexml_load_string($xmlString);
        $ambiente = (string) $xml->infoTributaria->ambiente;

        if (!in_array($ambiente, ['1', '2'])) {
            throw new \Exception("Ambiente inválido: $ambiente");
        }

        return $ambiente;
    }
}
