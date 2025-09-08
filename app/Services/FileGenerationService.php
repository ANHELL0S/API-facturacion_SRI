<?php

namespace App\Services;

use App\Models\Comprobante;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Enums\EstadosComprobanteEnum;
use App\Exceptions\SriException;
use PDF;

class FileGenerationService
{
    protected $sriService;

    public function __construct(SriComprobanteService $sriService)
    {
        $this->sriService = $sriService;
    }

    public function generateXmlContent(Comprobante $comprobante): string
    {
        if (trim(strtolower($comprobante->estado)) !== EstadosComprobanteEnum::AUTORIZADO->value) {
            throw new SriException('No es posible obtener el XML porque el comprobante no ha sido autorizado por el SRI');
        }

        Gate::authorize('viewXml', $comprobante);

        $ambiente = strval($comprobante->ambiente);

        return $this->sriService->consultarXmlAutorizado($comprobante->clave_acceso, $ambiente);
    }

    public function generatePdfContent(Comprobante $comprobante, &$fileName): string
    {
        if (trim(strtolower($comprobante->estado)) !== EstadosComprobanteEnum::AUTORIZADO->value) {
            throw new SriException('No es posible generar el PDF porque el comprobante no ha sido autorizado por el SRI');
        }

        Gate::authorize('view', $comprobante);

        $clave_acceso = $comprobante->clave_acceso;
        $facturaNumero = $comprobante->establecimiento . '-' . $comprobante->punto_emision . '-' . $comprobante->secuencial;
        $fileName = 'FAC-' . $facturaNumero . '.pdf';
        $cachedPdfPath = "pdfs/{$clave_acceso}.pdf";

        if (Storage::disk('public')->exists($cachedPdfPath)) {
            return Storage::disk('public')->get($cachedPdfPath);
        }

        $barcodePath = "barcodes/{$clave_acceso}.png";
        if (!Storage::disk('public')->exists($barcodePath)) {
            $pngBase64 = ClaveAccesoBarcode::makeBase64($clave_acceso);
            $pngBinary = base64_decode($pngBase64);
            Storage::disk('public')->put($barcodePath, $pngBinary);
        }

        $xmlString = $this->generateXmlContent($comprobante);
        $xmlObject = simplexml_load_string($xmlString);

        $data = [
            'numeroAutorizacion' => $comprobante->clave_acceso,
            'fechaAutorizacion' => $comprobante->fecha_autorizacion,
            'infoTributaria' => $xmlObject->infoTributaria,
            'infoFactura' => $xmlObject->infoFactura,
            'detalles' => $xmlObject->detalles->detalle,
            'infoAdicional' => $xmlObject->infoAdicional ?? null,
            'logo_path' => $comprobante->user->logo_path ?? null,
            'user' => $comprobante->user,
            'barcode_path' => storage_path('app/public/' . $barcodePath),
        ];

        $pdf = PDF::loadView('pdf.invoice', $data);
        $pdfContent = $pdf->output();

        Storage::disk('public')->put($cachedPdfPath, $pdfContent);

        return $pdfContent;
    }
}
