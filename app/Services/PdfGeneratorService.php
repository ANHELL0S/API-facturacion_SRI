<?php

namespace App\Services;

use App\Models\Comprobante;
use App\Services\ClaveAccesoBarcode;
use App\Services\SriComprobanteService;
use Illuminate\Support\Facades\Storage;
use PDF;

class PdfGeneratorService
{
    protected $sriService;

    public function __construct(SriComprobanteService $sriService)
    {
        $this->sriService = $sriService;
    }

    public function generate(Comprobante $comprobante, string $disk = 'temp'): string
    {
        $clave_acceso = $comprobante->clave_acceso;

        // Generate and save barcode if it doesn't exist
        $barcodePath = "barcodes/{$clave_acceso}.png";
        if (!Storage::disk('public')->exists($barcodePath)) {
            $pngBase64 = ClaveAccesoBarcode::makeBase64($clave_acceso);
            $pngBinary = base64_decode($pngBase64);
            Storage::disk('public')->put($barcodePath, $pngBinary);
        }

        // Get the environment from the comprobante
        $ambiente = strval($comprobante->ambiente);

        // Query the XML from SRI
        $xmlString = $this->sriService->consultarXmlAutorizado($clave_acceso, $ambiente);

        // Parse the XML
        $xmlObject = simplexml_load_string($xmlString);

        // Extract data for the view
        $data = [
            'numeroAutorizacion' => $comprobante->clave_acceso,
            'fechaAutorizacion' => $comprobante->fecha_autorizacion,
            'infoTributaria' => $xmlObject->infoTributaria,
            'infoFactura' => $xmlObject->infoFactura,
            'detalles' => $xmlObject->detalles->detalle,
            'infoAdicional' => $xmlObject->infoAdicional ?? null,
            'logo_path' => $comprobante->user->logo_path ?? null,
            'user' => $comprobante->user,
            'barcode_path' => Storage::disk('public')->path($barcodePath),
        ];

        // Generate the PDF
        $pdf = PDF::loadView('pdf.invoice', $data);

        // Save the PDF
        $facturaNumero = $xmlObject->infoTributaria->estab . '-' . $xmlObject->infoTributaria->ptoEmi . '-' . $xmlObject->infoTributaria->secuencial;
        $fileName = 'FAC-' . $facturaNumero . '.pdf';

        if ($disk === 'public') {
            $directory = storage_path('app/public/invoices');
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            $path = $directory . '/' . $fileName;
        } else {
            $directory = storage_path('app/temp');
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            $path = $directory . '/' . $fileName;
        }

        $pdf->save($path);

        return $path;
    }
}
