<?php

namespace App\Services;

use App\Models\Comprobante;
use App\Models\User;
use App\Models\PuntoEmision;
use App\Enums\TipoComprobanteEnum;
use App\Enums\EstadosComprobanteEnum;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Exceptions\SriException;

class SincronoComprobanteService
{
    protected $comprobanteGenerator;
    protected $emittoEmailService;
    protected $pdfGenerator;
    protected $sriComprobanteService;

    public function __construct(
        ComprobanteGenerator $comprobanteGenerator,
        EmittoEmailService $emittoEmailService,
        PdfGeneratorService $pdfGenerator,
        SriComprobanteService $sriComprobanteService
    ) {
        $this->comprobanteGenerator = $comprobanteGenerator;
        $this->emittoEmailService = $emittoEmailService;
        $this->pdfGenerator = $pdfGenerator;
        $this->sriComprobanteService = $sriComprobanteService;
    }

    public function procesarComprobante(array $validated_data, User $user, PuntoEmision $puntoEmision, TipoComprobanteEnum $type): Comprobante
    {
        $comprobante = null;
        $signedFilePath = null;

        try {
            $comprobante = \DB::transaction(function () use ($validated_data, $user, $puntoEmision, $type, &$signedFilePath) {
                // 1. Preparar datos y secuencial
                $preparedData = app(DocumentData::class)->prepareData($puntoEmision, $user, $validated_data);

                // 2. Generar XML
                $generado = $this->comprobanteGenerator->factura($preparedData, $user, $puntoEmision);
                $claveAcceso = $generado['accessKey'];
                $xmlContent = $generado['xml'];

                // 3. Guardar XML temporalmente
                $uniqueId = Str::uuid()->toString();
                $xmlFilePath = $this->guardarXMLTemporal($xmlContent, $type->value, $uniqueId);

                // 4. Firmar XML
                $signedFilePath = $this->firmarComprobante($xmlFilePath, $user, $uniqueId);

                // 5. Enviar y autorizar
                $response = $this->sriComprobanteService->enviarYAutorizarComprobante(
                    file_get_contents($signedFilePath),
                    $claveAcceso
                );

                if (!$response['success']) {
                    throw new SriException($response['error'] ?? 'El comprobante no fue autorizado');
                }

                $autorizacion = $response['autorizacion'];

                // 6. Crear el registro del comprobante en la base de datos
                $comprobante = Comprobante::create([
                    'user_id' => $user->id,
                    'tipo_comprobante' => $type->value,
                    'ambiente' => $user->ambiente,
                    'cliente_email' => $validated_data['infoAdicional']['email'] ?? null,
                    'cliente_ruc' => $validated_data['identificacionComprador'],
                    'fecha_emision' => $validated_data['fechaEmision'],
                    'payload' => json_encode($validated_data),
                    'estado' => EstadosComprobanteEnum::AUTORIZADO->value,
                    'clave_acceso' => $claveAcceso,
                    'establecimiento' => $preparedData['estab'],
                    'punto_emision' => $preparedData['ptoEmi'],
                    'secuencial' => $preparedData['secuencial'],
                    'procesado_en' => now(),
                    'fecha_autorizacion' => $autorizacion->fechaAutorizacion ?? now(),
                ]);

                // 7. Actualizar secuencial del punto de emisión
                $puntoEmision->ultimoSecuencial = $preparedData['secuencial'];
                $proximo = (int)$puntoEmision->proximo_secuencial + 1;
                $puntoEmision->proximo_secuencial = str_pad($proximo, 9, '0', STR_PAD_LEFT);
                $puntoEmision->save();

                return $comprobante;
            });

            // 8. Enviar correo (fuera de la transacción)
            if ($comprobante && $user->enviar_factura_por_correo) {
                $this->enviarCorreo($comprobante, $user, $signedFilePath, $validated_data);
            }

            return $comprobante;
        } catch (\Throwable $e) {
            // Si algo falla, la transacción hará rollback automáticamente.
            Log::error("Error en procesamiento síncrono del comprobante: " . $e->getMessage());
            // Envolver en SriException para que el controlador lo maneje como un error de negocio y no un 500.
            throw new SriException('0', $e->getMessage());
        } finally {
            if ($signedFilePath && file_exists($signedFilePath)) {
                @unlink($signedFilePath);
            }
        }
    }

    private function guardarXMLTemporal(string $xml, string $tipo, string $uniqueId): string
    {
        $xmlDir = storage_path('app/temp/comprobantes');
        if (!file_exists($xmlDir)) {
            mkdir($xmlDir, 0777, true);
        }
        $xmlFilePath = $xmlDir . '/' . $tipo . '_temporal_' . $uniqueId . '.xml';
        file_put_contents($xmlFilePath, $xml);
        return $xmlFilePath;
    }

    private function firmarComprobante(string $xmlFilePath, User $user, string $uniqueId): string
    {
        try {
            $password = decrypt($user->signature_key);
            $signatureFilePath = storage_path('app/private/' . $user->signature_path);
            $outputDir = storage_path('app/temp/comprobantes_firmados');
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0777, true);
            }
            $outputFile = 'signed_' . $uniqueId . '.xml';
            $signedFilePath = $outputDir . '/' . $outputFile;

            $command = "java -jar " . escapeshellarg(base_path('app/firmador/sri-fat.jar')) . " " .
                escapeshellarg($signatureFilePath) . " " .
                escapeshellarg($password) . " " .
                escapeshellarg($xmlFilePath) . " " .
                escapeshellarg($outputDir) . " " . escapeshellarg($outputFile);

            exec($command, $output, $return_var);

            if ($return_var !== 0 || !file_exists($signedFilePath)) {
                Log::error('Error ejecutando el firmador.', ['output' => $output]);
                throw new \Exception('Error ejecutando el firmador.');
            }

            return $signedFilePath;
        } catch (\Exception $e) {
            throw new \Exception('Error al firmar el XML: ' . $e->getMessage());
        } finally {
            if (file_exists($xmlFilePath)) {
                @unlink($xmlFilePath);
            }
        }
    }

    private function enviarCorreo(Comprobante $comprobante, User $user, string $signedFilePath, array $payload)
    {
        try {
            $recipientEmail = $payload['infoAdicional']['email'] ?? null;
            if (!$recipientEmail) return;

            $numeroComprobante = "{$comprobante->establecimiento}-{$comprobante->punto_emision}-{$comprobante->secuencial}";
            $subject = "Ha recibido su documento electrónico: FAC {$numeroComprobante}";

            $pdfPath = $this->pdfGenerator->generate($comprobante, 'public');
            $relativePath = str_replace(storage_path('app/public') . '/', '', $pdfPath);
            $pdfUrl = Storage::disk('public')->url($relativePath);
            $logoUrl = $user->logo_path ? Storage::disk('public')->url($user->logo_path) : null;

            $emailData = [
                'logoUrl' => $logoUrl,
                'claveAcceso' => $comprobante->clave_acceso,
                'total' => $payload['importeTotal'] ?? 0.0,
                'pdfUrl' => $pdfUrl,
            ];

            $message = view('emails.invoice', $emailData)->render();
            $attachments = [
                ['filename' => "{$comprobante->clave_acceso}.xml", 'path' => $signedFilePath],
                ['filename' => "{$comprobante->clave_acceso}.pdf", 'path' => $pdfPath],
            ];

            $this->emittoEmailService->sendInvoiceEmail($user, $recipientEmail, $subject, $message, $attachments);
        } catch (\Exception $e) {
            Log::error("Error al intentar enviar el correo para el comprobante {$comprobante->clave_acceso}: " . $e->getMessage());
        }
    }
}
