<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use stdClass;


class ServicioFirmaXml
{

    public ?string $firmador;
    public ?string $comprobantes_path;
    public ?string $save_xml_signed;
    public ?string $firmados_path;
    public ?string $storage_disk;

    public function __construct()
    {
        $this->firmador = config('signing.jar');
        $this->firmados_path = config('signing.firmados');
        $this->save_xml_signed = config('signing.ruta_firmados_absoluta');
        $this->comprobantes_path = config('signing.comprobantes');
        $this->storage_disk = config('signing.storage_disk');
    }

    /**
     * Guardar el comprobante sin firmar
     *
     * @param string $xml
     * @param string $clave_acceso
     * @return string Retorna la ruta del comprobante sin firmar
     */
    public function guardar_comprobante(string $xml, string $clave_acceso): string
    {
        Storage::disk($this->storage_disk)
            ->put($this->comprobantes_path . "/{$clave_acceso}.xml", $xml);

        if (Storage::disk($this->storage_disk)->exists($this->comprobantes_path . "/{$clave_acceso}.xml")) {
            return Storage::disk($this->storage_disk)
                ->path($this->comprobantes_path . "/{$clave_acceso}.xml");
        } else {
            throw new \Exception("Error al guardar el comprobante electrónico", 501);
        }
    }

    /**
     * Guardar el comprobante firmado
     *
     * @param string $xml_signed
     * @param string $clave_acceso
     * @return void
     */
    function guardar_comprobante_firmado(string $xml_signed, string $clave_acceso): void
    {
        file_put_contents("{$this->save_xml_signed}/{$clave_acceso}.xml", $xml_signed);
    }

    /**
     * Firmar el comprobante
     *
     * @param string $xml
     * @param string $certificado
     * @param string $psw
     * @return stdClass|array Respuesta con el comprobante firmado
     */
    public function firmar_comprobante(string $xml, string $certificado, string $psw): stdClass|array
    {
        $output = (new XmlSign(
            cert: $certificado,
            password: $psw,
            xml: $xml
        ))->signXml();

        return $output;
    }

    /**
     * Obtener el comprobante firmado
     *
     * @param string $clave_acceso
     * @return string
     */
    public function obtener_comprobante_firmado(string $clave_acceso): string
    {
        $xml = Storage::disk(name: $this->storage_disk)
            ->get(path: $this->firmados_path . "/{$clave_acceso}.xml");
        return $xml;
    }

    /**
     * Método que permite firmar el comprobante
     *
     * @param string $xml
     * @param string $clave_acceso
     * @param string $certificado
     * @param string $psw
     * @return stdClass|array Información del comprobante firmado o errores en firma
     */
    public static function firmarXml(string $xml, string $clave_acceso, string $certificado, string $psw): stdClass|array
    {
        $firmador = new static();
        try {
            $firmador->guardar_comprobante(xml: $xml, clave_acceso: $clave_acceso);
            // $firmar = $firmador->firmar_comprobante(xml: $xml, certificado: $certificado, psw: $psw, $clave_acceso);
            $firmar = $firmador->firmar_comprobante(xml: $xml, certificado: $certificado, psw: $psw);
            if ($firmar->status == 200) {
                $firmador->guardar_comprobante_firmado(xml_signed: $firmar->xml_signed, clave_acceso: $clave_acceso);
                return $firmar;
            } else {
                throw new \Exception(message: $firmar->message ?? "Error al firmar el comprobante", code: 500);
            }
        } catch (\Exception $e) {
            $response = new stdClass();
            $response->status = 500;
            $response->message = "Error al firmar el comprobante";
            $response->message_error = $e->getMessage();
            $response->errors = [
                $e->getTrace()
            ];
            return $response;
        }
    }
}
