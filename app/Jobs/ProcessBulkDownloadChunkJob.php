<?php

namespace App\Jobs;

use App\Models\BulkDownloadJob;
use App\Models\Comprobante;
use App\Services\FileGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\Bus\Batchable;

class ProcessBulkDownloadChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bulkDownloadJob;
    public $clavesAccesoChunk;

    /**
     * Create a new job instance.
     */
    public function __construct(BulkDownloadJob $bulkDownloadJob, array $clavesAccesoChunk)
    {
        $this->bulkDownloadJob = $bulkDownloadJob;
        $this->clavesAccesoChunk = $clavesAccesoChunk;
    }

    /**
     * Execute the job.
     */
    public function handle(FileGenerationService $fileGenerationService): void
    {
        // Eager load the user relationship to ensure it's available
        $this->bulkDownloadJob->load('user');

        // Authenticate as the user who initiated the job
        if ($this->bulkDownloadJob->user) {
            Auth::login($this->bulkDownloadJob->user);
        } else {
            Log::error("Bulk download chunk job failed: User not found for job ID {$this->bulkDownloadJob->id}");
            $this->fail(new \Exception("User not found for job ID {$this->bulkDownloadJob->id}"));
            return;
        }

        if ($this->batch()->cancelled()) {
            Auth::logout();
            return;
        }

        $tempDirectory = 'bulk-downloads/' . $this->bulkDownloadJob->id;

        foreach ($this->clavesAccesoChunk as $claveAcceso) {
            try {
                $comprobante = Comprobante::findByClaveAcceso($claveAcceso);
                if (!$comprobante) {
                    Log::warning("Comprobante not found for clave de acceso: {$claveAcceso} in job {$this->bulkDownloadJob->id}");
                    continue;
                }

                $fileName = '';
                if ($this->bulkDownloadJob->format === 'pdf') {
                    $content = $fileGenerationService->generatePdfContent($comprobante, $fileName);
                    Storage::disk('local')->put($tempDirectory . '/' . $fileName, $content);
                } else {
                    $fileName = $claveAcceso . '.xml';
                    $content = $fileGenerationService->generateXmlContent($comprobante);
                    Storage::disk('local')->put($tempDirectory . '/' . $fileName, $content);
                }

                $this->bulkDownloadJob->increment('processed_files');

            } catch (Throwable $e) {
                Log::error("Error processing file for bulk download job {$this->bulkDownloadJob->id}: " . $e->getMessage());
            }
        }
    }
}
