<?php

namespace App\Services;

use App\Models\PuntoEmision;
use App\Models\User;
use App\Services\DocumentGenerator;
use App\Services\XmlValidator;
use App\Services\DocumentData;
use Illuminate\Support\Facades\Log;

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

    public function factura(array $data, User $user, PuntoEmision $puntoEmision, string $claveAcceso)
    {
        try {
            // Preparar datos
            $prepared = $this->documentData->prepareData($puntoEmision, $user, $data);

            // 🔥 PASAR LA CLAVE DE ACCESO AL GENERADOR DE DOCUMENTOS
            $generado = $this->documentGenerator->newFactura($prepared, $claveAcceso);

            // Validar XML
            $this->xmlValidator::validateComprobante($generado['xml'], "factura", "2.1.0");
            Log::info('Comprobante generado y validado correctamente');

            return $generado;
        } catch (\Exception $e) {
            throw new \Exception('Error en el generador de comprobante: ' . $e->getMessage());
        }
    }
}
