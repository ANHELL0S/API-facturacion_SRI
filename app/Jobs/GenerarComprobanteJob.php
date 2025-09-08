<?php

namespace App\Jobs;

use App\Exceptions\SriException;
use App\Services\ComprobanteGenerator;
use App\Services\DocumentData;
use App\Models\Comprobante;
use App\Models\User;
use App\Services\SriComprobanteService;
use App\Enums\TipoComprobanteEnum;
use App\Enums\EstadosComprobanteEnum;
use App\Models\PuntoEmision;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\EmittoEmailService;
use App\Services\PdfGeneratorService;

class GenerarComprobanteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [60, 300, 900];

    protected Comprobante $comprobante;
    protected User $user;
    protected PuntoEmision $puntoEmision;
    protected TipoComprobanteEnum $type;
    protected $uniqueId;
    protected $claveAcceso;

    public function __construct(Comprobante $comprobante, User $user, PuntoEmision $puntoEmision, TipoComprobanteEnum $type,  string $claveAcceso)
    {
        $this->comprobante = $comprobante;
        $this->user = $user;
        $this->puntoEmision = $puntoEmision;
        $this->type = $type;
        $this->claveAcceso = $claveAcceso;
    }

    private function prepararDatosComprobante(PuntoEmision $puntoEmision, array $data = []): array
    {
        $preparedData = app(DocumentData::class)->prepareData($puntoEmision, $this->user, $data);

        // 🔥 EXTRAER EL SECUENCIAL DE LA CLAVE DE ACCESO
        // La estructura de la clave es: FECHA(8) + TIPO(2) + RUC(13) + AMBIENTE(1) + SERIE(6) + SECUENCIAL(9) + ...
        $secuencialFromKey = substr($this->claveAcceso, 30, 9); // 8+2+13+1+6 = 30 posición inicial

        // 🔥 USAR EL SECUENCIAL DE LA CLAVE EN LUGAR DEL GENERADO
        $preparedData['secuencial'] = $secuencialFromKey;

        return $preparedData;
    }

    private function actualizarSecuencial(PuntoEmision $puntoEmision): void
    {
        $secuencialFromKey = substr($this->claveAcceso, 30, 9);
        $puntoEmision->ultimoSecuencial = $secuencialFromKey;
        $puntoEmision->save();

        Log::info("📝 Secuencial actualizado a: {$secuencialFromKey} (sin incrementar)");
    }


    // 🔥 ACTUALIZAR LA TRANSACCIÓN PARA USAR EL NUEVO MÉTODO
    private function bloquearPuntoEmision(): PuntoEmision
    {
        return PuntoEmision::where('id', $this->puntoEmision->id)->lockForUpdate()->first();
    }



    private function generarXML(ComprobanteGenerator $comprobanteGenerator, array $preparedData, PuntoEmision $puntoEmision): array
    {
        try {
            return $comprobanteGenerator->factura($preparedData, $this->user, $puntoEmision, $this->claveAcceso);
        } catch (\Exception $e) {
            throw new \Exception('Error en el generador de comprobante: ' . $e->getMessage());
        }
    }


    private function guardarXMLTemporal(string $xml, string $tipo): string
    {
        $xmlDir = storage_path('app/temp/comprobantes');
        if (!file_exists($xmlDir)) {
            mkdir($xmlDir, 0777, true);
        }
        $this->uniqueId = Str::uuid()->toString();
        $xmlFilePath = $xmlDir . '/' . $tipo . '_' . $this->comprobante->id . '_' . $this->uniqueId . '.xml';
        file_put_contents($xmlFilePath, $xml);
        return $xmlFilePath;
    }

    private function firmarXMLTemporal(string $xmlFilePath): string
    {
        try {
            // Validaciones adicionales
            if (!$this->user->signature_key || !$this->user->signature_path) {
                throw new \Exception('Firma electrónica no configurada para el usuario');
            }

            $password = decrypt($this->user->signature_key);
            $signatureFilePath = storage_path('app/private/' . $this->user->signature_path);

            if (!file_exists($signatureFilePath)) {
                throw new \Exception('Archivo de firma no encontrado: ' . $signatureFilePath);
            }

            $outputDir = storage_path('app/temp/comprobantes_firmados');
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $outputFile = 'signed_' . $this->uniqueId . '.xml';
            $signedFilePath = $outputDir . '/' . $outputFile;

            $command = "java -jar " . escapeshellarg(base_path('app/firmador/sri-fat.jar')) . " " .
                escapeshellarg($signatureFilePath) . " " .
                escapeshellarg($password) . " " .
                escapeshellarg($xmlFilePath) . " " .
                escapeshellarg($outputDir) . " " . escapeshellarg($outputFile);

            exec($command, $output, $return_var);

            if ($return_var !== 0 || !file_exists($signedFilePath)) {
                Log::error('Error ejecutando el firmador.', ['output' => $output, 'return_var' => $return_var]);
                throw new \Exception('Error ejecutando el firmador. Código: ' . $return_var);
            }

            return $signedFilePath;
        } catch (\Exception $e) {
            throw new \Exception('Error al firmar el XML: ' . $e->getMessage());
        }
    }

    private function firmarComprobante(string $xmlFilePath): string
    {
        try {
            return $this->firmarXMLTemporal($xmlFilePath);
        } catch (\Exception $e) {
            Log::error("Error durante la firma del XML: " . $e->getMessage());
            throw $e;
        }
    }

    private function actualizarEstadoFirmado(array $preparedData): void
    {
        $this->comprobante->update([
            'estado' => EstadosComprobanteEnum::FIRMADO->value,
            'clave_acceso' => $this->claveAcceso,
            'establecimiento' => $preparedData['estab'],
            'punto_emision' => $preparedData['ptoEmi'],
            'secuencial' => $preparedData['secuencial'],
            'procesado_en' => now(),
        ]);
    }

    private function autorizarComprobanteFirmado(string $signedFilePath, EmittoEmailService $emittoEmailService, PdfGeneratorService $pdfGenerator): void
    {
        try {
            $sriSender = new SriComprobanteService();
            $response = $sriSender->enviarYAutorizarComprobante(
                file_get_contents($signedFilePath),
                $this->claveAcceso
            );

            if (!$response['success']) {
                throw new SriException($response['error'] ?? 'El comprobante no fue recibido');
            }

            $autorizacion = $response['autorizacion'];

            $this->comprobante->update([
                'estado' => EstadosComprobanteEnum::AUTORIZADO->value,
                'fecha_autorizacion' => $autorizacion->fechaAutorizacion ?? now(),
            ]);

            Log::info("✅ Comprobante autorizado: {$this->claveAcceso}");

            // 🔥 INCREMENTAR SECUENCIAL SOLO DESPUÉS DE AUTORIZACIÓN EXITOSA
            $this->incrementarSecuencial();

            // 🔥 NUEVO: Enviar callback al sistema externo
            $this->enviarCallbackSistemaExterno(true, [
                'estado_sri' => 'AUTORIZADO',
                'clave_acceso' => $this->claveAcceso,
                'numero_autorizacion' => $autorizacion->numeroAutorizacion ?? null,
                'fecha_autorizacion' => $autorizacion->fechaAutorizacion ?? now(),
            ]);

            // Envío de email (no crítico para el proceso)
            $this->enviarEmailComprobante($signedFilePath, $emittoEmailService, $pdfGenerator);
        } catch (SriException $e) {
            $errorMessage = $e->getMessage();
            if (Str::contains($errorMessage, ['SRI no disponible', 'Parsing WSDL', 'timeout'])) {
                Log::warning("SRI no disponible. Reintentando... [{$this->claveAcceso}]");
                $this->release(60); // Reintentar en 60 segundos
                return;
            }

            $this->comprobante->update([
                'estado' => EstadosComprobanteEnum::NECESITA_CORRECCION->value,
                'error_message' => $errorMessage
            ]);

            // 🔥 NUEVO: Enviar callback de error
            $this->enviarCallbackSistemaExterno(false, [
                'estado_sri' => 'NECESITA_CORRECCION',
                'error_message' => $errorMessage,
            ]);

            Log::error("⚠️ Comprobante requiere corrección [{$this->claveAcceso}]: " . $errorMessage);
        } catch (\Exception $e) {
            $this->comprobante->update([
                'estado' => EstadosComprobanteEnum::FALLIDO->value,
                'error_message' => $e->getMessage()
            ]);

            // 🔥 NUEVO: Enviar callback de error
            $this->enviarCallbackSistemaExterno(false, [
                'estado_sri' => 'FALLIDO',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("🔥 Error inesperado en autorización [{$this->claveAcceso}]: " . $e->getMessage());
        }
    }

    /**
     * 🔥 NUEVO MÉTODO: Incrementar secuencial solo después de autorización exitosa
     */
    private function incrementarSecuencial(): void
    {
        try {
            $puntoEmision = PuntoEmision::find($this->puntoEmision->id);
            $currentSecuencial = (int) $puntoEmision->ultimoSecuencial;
            $proximo = $currentSecuencial + 1;
            $puntoEmision->proximo_secuencial = str_pad($proximo, 9, '0', STR_PAD_LEFT);
            $puntoEmision->save();

            Log::info("✅ Secuencial incrementado: {$currentSecuencial} -> {$proximo}");
        } catch (\Exception $e) {
            Log::error("Error incrementando secuencial: " . $e->getMessage());
        }
    }


    /**
     * Enviar callback al sistema externo que solicitó la facturación
     */
    private function enviarCallbackSistemaExterno(bool $success, array $data): void
    {
        try {
            $payload = json_decode($this->comprobante->payload, true);
            $saleId = $payload['saleId'] ?? null;
            $callbackUrl = $payload['callbackUrl'] ?? null;

            if (!$callbackUrl || !$saleId) {
                Log::info("No se encontró callback URL o saleId para comprobante {$this->comprobante->id}");
                return;
            }

            $callbackData = [
                'saleId' => $saleId,
                'comprobanteId' => $this->comprobante->id,
                'success' => $success,
                'timestamp' => now()->toISOString(),
                ...$data
            ];

            Log::info("🔗 Enviando callback a: {$callbackUrl}", [
                'comprobante_id' => $this->comprobante->id,
                'saleId' => $saleId,
                'estado' => $this->comprobante->estado
            ]);

            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $response = $client->post($callbackUrl, [
                'json' => $callbackData,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Billing-Notification' => 'true',
                    'User-Agent' => 'Laravel-Billing-Service/1.0',
                    'X-Comprobante-Id' => $this->comprobante->id
                ]
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info("✅ Callback enviado exitosamente", [
                    'url' => $callbackUrl,
                    'status' => $statusCode,
                    'comprobante_id' => $this->comprobante->id
                ]);
            } else {
                Log::warning("⚠️ Callback enviado pero con status inesperado", [
                    'url' => $callbackUrl,
                    'status' => $statusCode,
                    'response' => $response->getBody()->getContents()
                ]);
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error("❌ Error de red enviando callback", [
                'url' => $callbackUrl ?? 'null',
                'error' => $e->getMessage(),
                'comprobante_id' => $this->comprobante->id
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Error inesperado enviando callback", [
                'error' => $e->getMessage(),
                'comprobante_id' => $this->comprobante->id
            ]);
        }
    }

    // 🔥 TAMBIÉN MODIFICAR EL MÉTODO handle() PARA ENVIAR CALLBACK EN CASO DE ERROR GENERAL
    public function handle(ComprobanteGenerator $comprobanteGenerator, EmittoEmailService $emittoEmailService, PdfGeneratorService $pdfGenerator)
    {
        Log::info("Iniciando generación del comprobante ID: {$this->comprobante->id} con clave: {$this->claveAcceso}");

        $this->comprobante->update(['estado' => EstadosComprobanteEnum::PROCESANDO->value]);

        $signedFilePath = null;
        $xmlFilePath = null;

        try {
            // --- Atomic transaction for sequential number ---
            $preparedData = DB::transaction(function () {
                $puntoEmisionLocked = $this->bloquearPuntoEmision();
                $preparedData = $this->prepararDatosComprobante($puntoEmisionLocked, json_decode($this->comprobante->payload, true));

                // 🔥 SOLO ACTUALIZAR, NO INCREMENTAR
                $this->actualizarSecuencial($puntoEmisionLocked);

                return $preparedData;
            }, 5);

            // Generate XML - USAR LA CLAVE EXISTENTE
            $generado = $this->generarXML($comprobanteGenerator, $preparedData, $this->puntoEmision);

            // ❌❌❌ ELIMINAR ESTA LÍNEA QUE SOBRESCRIBE LA CLAVE:
            // $this->claveAcceso = $generado['accessKey'];

            // Guardar XML temporal
            $xmlFilePath = $this->guardarXMLTemporal($generado['xml'], $this->type->value);

            // Firmar XML
            $signedFilePath = $this->firmarComprobante($xmlFilePath);

            // Actualizar estado del comprobante
            $this->actualizarEstadoFirmado($preparedData);

            // Enviar y autorizar
            $this->autorizarComprobanteFirmado($signedFilePath, $emittoEmailService, $pdfGenerator);
        } catch (\Throwable $e) {
            $this->comprobante->update([
                'estado' => EstadosComprobanteEnum::FALLIDO->value,
                'error_message' => $e->getMessage(),
            ]);

            $this->enviarCallbackSistemaExterno(false, [
                'estado_sri' => 'ERROR',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Error en generación del comprobante [ID {$this->comprobante->id}]: " . $e->getMessage());
            throw $e;
        } finally {
            // Limpiar archivos temporales
            if ($xmlFilePath && file_exists($xmlFilePath)) {
                @unlink($xmlFilePath);
            }
            if ($signedFilePath && file_exists($signedFilePath)) {
                @unlink($signedFilePath);
            }
        }
    }

    private function enviarEmailComprobante(string $signedFilePath, EmittoEmailService $emittoEmailService, PdfGeneratorService $pdfGenerator): void
    {
        try {
            if (!$this->user->enviar_factura_por_correo) {
                return;
            }

            $payload = json_decode($this->comprobante->payload, true);

            // Múltiples posibles ubicaciones del email
            $recipientEmail = $payload['infoAdicional']['email']
                ?? $payload['destinatario']['email']
                ?? $payload['cliente']['email']
                ?? null;

            if (!$recipientEmail || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning("Email no válido o no proporcionado para comprobante {$this->claveAcceso}");
                return;
            }

            $numeroComprobante = "{$this->comprobante->establecimiento}-{$this->comprobante->punto_emision}-{$this->comprobante->secuencial}";
            $subject = "Ha recibido su documento electrónico: FAC {$numeroComprobante}";

            // Generar PDF
            $pdfPath = $pdfGenerator->generate($this->comprobante, 'public');
            $relativePath = str_replace(storage_path('app/public') . '/', '', $pdfPath);
            $pdfUrl = Storage::disk('public')->url($relativePath);

            // Preparar datos para el email
            $logoUrl = null;
            if ($this->user->logo_path) {
                $logoUrl = Storage::disk('public')->url($this->user->logo_path);
            }

            $emailData = [
                'logoUrl' => $logoUrl,
                'claveAcceso' => $this->claveAcceso,
                'total' => $this->getImporteTotal($payload),
                'pdfUrl' => $pdfUrl,
            ];

            $message = view('emails.invoice', $emailData)->render();

            $attachments = [
                ['filename' => "{$this->claveAcceso}.xml", 'path' => $signedFilePath],
                ['filename' => "{$this->claveAcceso}.pdf", 'path' => $pdfPath],
            ];

            $emittoEmailService->sendInvoiceEmail($this->user, $recipientEmail, $subject, $message, $attachments);
            Log::info("📧 Email enviado para comprobante: {$this->claveAcceso}");
        } catch (\Exception $e) {
            Log::error("Error al enviar email para comprobante {$this->claveAcceso}: " . $e->getMessage());
            // No relanzar la excepción para no fallar el proceso completo
        }
    }

    private function getImporteTotal(array $payload): float
    {
        if (isset($payload['importeTotal'])) {
            return (float) $payload['importeTotal'];
        }

        if (isset($payload['valorModificacion'])) {
            return (float) $payload['valorModificacion'];
        }

        if (isset($payload['totalSinImpuestos'])) {
            return (float) $payload['totalSinImpuestos'];
        }

        return 0.0;
    }
}
