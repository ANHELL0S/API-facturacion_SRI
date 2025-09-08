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
use App\Enums\BulkDownloadStatusEnum;
use Throwable;
use ZipArchive;

class CreateBulkDownloadZipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bulkDownloadJob;
    public $clavesAcceso;

    /**
     * Create a new job instance.
     */
    public function __construct(BulkDownloadJob $bulkDownloadJob, array $clavesAcceso)
    {
        $this->bulkDownloadJob = $bulkDownloadJob;
        $this->clavesAcceso = $clavesAcceso;
    }

    /**
     * Execute the job.
     */
    public function handle(FileGenerationService $fileGenerationService): void
    {
        Auth::login($this->bulkDownloadJob->user);

        $this->bulkDownloadJob->update(['status' => BulkDownloadStatusEnum::PROCESSING]);

        $zipFileName = 'bulk-downloads/bulk-download-' . $this->bulkDownloadJob->id . '.zip';
        $tempZipPath = tempnam(sys_get_temp_dir(), 'zip');

        try {
            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception('Cannot create zip file.');
            }

            $processedCount = 0;
            foreach ($this->clavesAcceso as $claveAcceso) {
                try {
                    $comprobante = Comprobante::findByClaveAcceso($claveAcceso);
                    if (!$comprobante) {
                        Log::warning("Comprobante not found for clave de acceso: {$claveAcceso} in job {$this->bulkDownloadJob->id}");
                        continue;
                    }

                    if ($this->bulkDownloadJob->format === 'pdf') {
                        $fileName = '';
                        $content = $fileGenerationService->generatePdfContent($comprobante, $fileName);
                        $zip->addFromString($fileName, $content);
                    } else {
                        $content = $fileGenerationService->generateXmlContent($comprobante);
                        $zip->addFromString($claveAcceso . '.xml', $content);
                    }

                    $processedCount++;
                    $this->bulkDownloadJob->update(['processed_files' => $processedCount]);

                } catch (Throwable $e) {
                    Log::error("Error processing file for bulk download job {$this->bulkDownloadJob->id}: " . $e->getMessage());
                }
            }

            $zip->close();

            Storage::disk('public')->put($zipFileName, file_get_contents($tempZipPath));

            $this->bulkDownloadJob->update([
                'status' => BulkDownloadStatusEnum::COMPLETED,
                'file_path' => $zipFileName,
                'expires_at' => now()->addDay(),
            ]);

        } catch (Throwable $e) {
            $this->fail($e);
        } finally {
            if (file_exists($tempZipPath)) {
                unlink($tempZipPath);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $this->bulkDownloadJob->update(['status' => BulkDownloadStatusEnum::FAILED]);
        Log::error("Bulk download job failed for job ID {$this->bulkDownloadJob->id}: " . $exception->getMessage());
    }
}
