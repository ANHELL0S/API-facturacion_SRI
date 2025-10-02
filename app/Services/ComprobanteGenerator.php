<?php

namespace App\Services;

use App\Models\PuntoEmision;
use App\Models\User;
use App\Services\DocumentGenerator;
use App\Services\XmlValidator;
use App\Services\DocumentData;
use Illuminate\Support\Facades\Log;

/**
 * Summary of ComprobanteGenerator
 */
class ComprobanteGenerator
{
    private $documentGenerator;
    private $xmlValidator;
    private $documentData;

    public function __construct(DocumentGenerator $documentGenerator, XmlValidator $xmlValidator, DocumentData $documentData)
    {
        $this->documentGenerator = $documentGenerator;
        $this->xmlValidator = $xmlValidator;
        $this->documentData = $documentData;
    }

    public function factura(array $data, User $user, PuntoEmision $puntoEmision)
    {
        try {
            // Preparar datos
            $prepared = $this->documentData->prepareData($puntoEmision, $user, $data);
            // Log::info('Datos preparados: ' . json_encode($prepared));

            // Generar XML y clave de acceso
            $generado = $this->documentGenerator->newFactura($prepared);
            // Log::info('Comprobante generado: ' . json_encode($generado));

            // Validar XML
            $this->xmlValidator::validateComprobante($generado['xml'], "factura", "2.1.0");
            Log::info('Comprobante generado y validado correctamente');

            return $generado;
        } catch(\Exception $e) {
            throw new \Exception('Error en el generador de comprobante: ' . $e->getMessage());
        }
    }
}