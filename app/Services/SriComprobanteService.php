<?php

namespace App\Services;

use App\Exceptions\SriException;
use SoapClient;
use SoapFault;
use Illuminate\Support\Facades\Log;

class SriComprobanteService
{
    private const RECEPCION_PRUEBAS = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
    private const RECEPCION_PRODUCCION = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
    private const AUTORIZACION_PRUEBAS = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
    private const AUTORIZACION_PRODUCCION = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';

    // Cache de clientes SOAP para reutilizar conexiones
    private static array $soapClients = [];

    /**
     * Obtiene o crea un cliente SOAP con configuración optimizada
     */
    private function getSoapClient(string $wsdl): SoapClient
    {
        if (!isset(self::$soapClients[$wsdl])) {
            self::$soapClients[$wsdl] = new SoapClient($wsdl, [
                'connection_timeout' => 3,
                'cache_wsdl' => WSDL_CACHE_BOTH,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                'keep_alive' => true,
                'exceptions' => true,
            ]);
        }
        return self::$soapClients[$wsdl];
    }

    /**
     * Envía un comprobante al SRI para su recepción.
     */
    public function enviarComprobanteRecepcion(string $xmlString): array
    {
        Log::info('⏳ Enviando comprobante a la recepción');

        $ambiente = $this->leerAmbienteDesdeXml($xmlString);
        $wsdl = ($ambiente == '1') ? self::RECEPCION_PRUEBAS : self::RECEPCION_PRODUCCION;

        try {
            $client = $this->getSoapClient($wsdl);
            $params = (object) ['xml' => $xmlString];
            $result = $client->validarComprobante($params);

            $estado = $result->RespuestaRecepcionComprobante->estado ?? null;

            if ($estado !== 'RECIBIDA') {
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
     * Optimizado: reduce intentos y tiempos de espera
     */
    public function enviarComprobanteAutorizacion(string $claveAcceso, string $ambiente = '1'): array
    {
        Log::info('⏳ Enviando comprobante para autorización');
        $wsdl = $ambiente === '1' ? self::AUTORIZACION_PRUEBAS : self::AUTORIZACION_PRODUCCION;

        try {
            $client = $this->getSoapClient($wsdl);
            $params = (object) ['claveAccesoComprobante' => $claveAcceso];

            // Reducido de 5 a 3 intentos máximos
            $maxIntentos = 3;
            $intentos = 0;

            while ($intentos < $maxIntentos) {
                $intentos++;

                try {
                    $result = $client->autorizacionComprobante($params);
                    $autorizaciones = $result->RespuestaAutorizacionComprobante->autorizaciones->autorizacion ?? null;

                    if ($autorizaciones) {
                        $autorizacion = is_array($autorizaciones) ? $autorizaciones[0] : $autorizaciones;

                        if ($autorizacion->estado === 'AUTORIZADO') {
                            Log::info("✅ Comprobante autorizado en intento {$intentos}");
                            return [
                                'success' => true,
                                'autorizacion' => $autorizacion,
                                'mensajes' => $autorizacion->mensajes ?? null
                            ];
                        }

                        // Si el estado es NO AUTORIZADO o RECHAZADO, no reintentar
                        if (in_array($autorizacion->estado, ['NO AUTORIZADO', 'RECHAZADO'])) {
                            $mensaje = $autorizacion->mensajes->mensaje ?? null;
                            $codigo = $mensaje->identificador ?? '0';
                            $descripcion = $mensaje->mensaje ?? 'Comprobante no autorizado';
                            $infoAdicional = $mensaje->informacionAdicional ?? null;

                            throw new SriException($codigo, $descripcion, [
                                'info_adicional' => $infoAdicional,
                                'estado_sri' => $autorizacion->estado,
                            ]);
                        }
                    }

                    // Solo esperar si no es el último intento y el estado es EN PROCESO
                    if ($intentos < $maxIntentos) {
                        usleep(500000); // 0.5 segundos (reducido de 1 segundo)
                    }
                } catch (SriException $e) {
                    throw $e; // Re-lanzar excepciones de SRI inmediatamente
                }
            }

            throw new SriException('0', 'No se recibió una respuesta de autorización válida del SRI después de ' . $maxIntentos . ' intentos.');
        } catch (SoapFault $e) {
            throw new SriException('0', 'Error de conexión con el SRI: ' . $e->getMessage());
        }
    }

    /**
     * Envía un comprobante al SRI y lo autoriza.
     */
    public function enviarYAutorizarComprobante(string $xmlString, string $claveAcceso): array
    {
        try {
            // Recepción
            $recepcion = $this->enviarComprobanteRecepcion($xmlString);

            if (!$recepcion['success']) {
                $estado = $recepcion['estado'] ?? null;
                $mensajes = $recepcion['mensajes'] ?? null;

                $codigo = '0';
                $mensaje = 'El comprobante no fue recibido';

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

            // Autorización inmediata (sin sleep innecesario)
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
     */
    public function consultarXmlAutorizado(string $claveAcceso, string $ambiente = '1'): string
    {
        Log::info("⏳ Consultando XML autorizado para clave de acceso: {$claveAcceso}");

        $wsdl = ($ambiente === '1') ? self::AUTORIZACION_PRUEBAS : self::AUTORIZACION_PRODUCCION;

        try {
            $client = $this->getSoapClient($wsdl);
            $params = (object) ['claveAccesoComprobante' => $claveAcceso];

            $result = $client->autorizacionComprobante($params);
            $autorizaciones = $result->RespuestaAutorizacionComprobante->autorizaciones->autorizacion ?? null;

            if ($autorizaciones) {
                $autorizacion = is_array($autorizaciones) ? $autorizaciones[0] : $autorizaciones;

                if ($autorizacion->estado === 'AUTORIZADO') {
                    return $autorizacion->comprobante;
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
     */
    private function leerAmbienteDesdeXml(string $xmlString): string
    {
        // Optimización: usar regex en lugar de parsear XML completo
        if (preg_match('/<ambiente>([12])<\/ambiente>/', $xmlString, $matches)) {
            return $matches[1];
        }

        // Fallback al método original si el regex falla
        $xml = simplexml_load_string($xmlString);
        $ambiente = (string) $xml->infoTributaria->ambiente;

        if (!in_array($ambiente, ['1', '2'])) {
            throw new \Exception("Ambiente inválido: $ambiente");
        }

        return $ambiente;
    }
}
