<?php

namespace App\Jobs;

use App\Enums\BulkDownloadStatusEnum;
use App\Models\BulkDownloadJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class CreateFinalZipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bulkDownloadJob;

    /**
     * Create a new job instance.
     */
    public function __construct(BulkDownloadJob $bulkDownloadJob)
    {
        $this->bulkDownloadJob = $bulkDownloadJob;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Eager load the user relationship to ensure it's available
        $this->bulkDownloadJob->load('user');

        // Authenticate as the user who initiated the job
        if ($this->bulkDownloadJob->user) {
            Auth::login($this->bulkDownloadJob->user);
        } else {
            Log::error("Create final zip job failed: User not found for job ID {$this->bulkDownloadJob->id}");
            $this->fail(new \Exception("User not found for job ID {$this->bulkDownloadJob->id}"));
            return;
        }

        // Set status to 'compressing' to give the user feedback
        $this->bulkDownloadJob->update(['status' => BulkDownloadStatusEnum::COMPRESSING]);

        $tempDirectory = 'bulk-downloads/' . $this->bulkDownloadJob->id;
        $zipFileName = 'bulk-downloads/bulk-download-' . $this->bulkDownloadJob->id . '.zip';
        $tempZipPath = tempnam(sys_get_temp_dir(), 'zip');

        try {
            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception('Cannot create zip file.');
            }

            $files = Storage::disk('local')->files($tempDirectory);
            foreach ($files as $file) {
                // Use addFile for memory efficiency. It streams the file from disk.
                $zip->addFile(Storage::disk('local')->path($file), basename($file));
            }

            $zip->close();

            Storage::disk('public')->put($zipFileName, file_get_contents($tempZipPath));

            $this->bulkDownloadJob->update([
                'status' => BulkDownloadStatusEnum::COMPLETED,
                'file_path' => $zipFileName,
                'expires_at' => now()->addDay(),
            ]);

        } catch (Throwable $e) {
            $this->bulkDownloadJob->update(['status' => BulkDownloadStatusEnum::FAILED]);
            Log::error("Failed to create final zip for job {$this->bulkDownloadJob->id}: " . $e->getMessage());
        } finally {
            if (file_exists($tempZipPath)) {
                unlink($tempZipPath);
            }
            // Clean up the temporary directory of individual files
            Storage::disk('local')->deleteDirectory($tempDirectory);
        }
    }
}
