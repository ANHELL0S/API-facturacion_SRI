<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmittoEmailService
{
    protected $baseUrl;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.emitto.base_url');
        $this->secretKey = config('services.emitto.secret');
    }

    /**
     * Sends an invoice email using the Emitto API as a multipart/form-data request.
     *
     * @param string $recipientEmail
     * @param string $subject
     * @param string $message
     * @param array $attachments Array of attachments, each with 'filename' and 'path' (local filesystem path).
     * @return bool
     * @throws \Exception
     */
    public function sendInvoiceEmail(User $user, string $recipientEmail, string $subject, string $message, array $attachments): bool
    {
        if (!$this->secretKey) {
            Log::error('EmittoEmailService: La clave secreta de Emitto no está configurada.');
            return false;
        }

        try {
            // Determine the 'from' address
            $fromEmail = $user->from_email ?? config('mail.from.address', 'noreply@example.com');

            $multipart = [
                [
                    'name'     => 'from',
                    'contents' => $fromEmail,
                ],
                [
                    'name'     => 'subjectEmail',
                    'contents' => $subject,
                ],
                [
                    'name'     => 'message',
                    'contents' => $message,
                ],
                [
                    'name'     => 'sendTo[0]',
                    'contents' => $recipientEmail,
                ]
            ];

            foreach ($attachments as $attachment) {
                if (isset($attachment['path']) && file_exists($attachment['path'])) {
                    $fileStream = @fopen($attachment['path'], 'r');
                    if ($fileStream === false) {
                        Log::error("EmittoEmailService: No se pudo abrir el archivo para adjuntar.", ['path' => $attachment['path']]);
                        continue; // Skip this attachment if it can't be opened
                    }

                    $multipart[] = [
                        'name'     => 'attachments',
                        'contents' => $fileStream,
                        'filename' => $attachment['filename'],
                    ];
                }
            }

            $response = Http::withHeaders([
                'x-key-emitto' => $this->secretKey,
                'Accept' => 'application/json',
            ])
            ->asMultipart()
            ->post("{$this->baseUrl}/email/send", $multipart);

            if ($response->failed()) {
                Log::error('EmittoEmailService: Falló el envío de correo (multipart).', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }

            Log::info('EmittoEmailService: Correo enviado exitosamente a ' . $recipientEmail);
            return true;

        } catch (\Exception $e) {
            Log::error('EmittoEmailService: Excepción al enviar correo (multipart).', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
