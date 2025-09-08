<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BulkDownloadJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CleanupGeneratedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-generated-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes old generated files like bulk download zips and cached PDFs.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of old generated files...');

        // --- Cleanup Bulk Download Jobs ---
        $expirationDate = Carbon::now()->subDay();
        $deletedJobs = 0;
        $oldJobs = BulkDownloadJob::where('created_at', '<', $expirationDate)->get();

        foreach ($oldJobs as $job) {
            if ($job->file_path && Storage::disk('public')->exists($job->file_path)) {
                Storage::disk('public')->delete($job->file_path);
                $this->line("Deleted zip file: {$job->file_path}");
            }
            $job->delete();
            $deletedJobs++;
        }
        $this->info("Cleanup complete for bulk download jobs. Deleted {$deletedJobs} old job(s).");

        // --- Cleanup Cached PDFs ---
        $this->info('Starting cleanup of old cached PDFs...');
        $deletedPdfs = 0;
        $pdfFiles = Storage::disk('public')->files('pdfs');
        $sevenDaysAgo = Carbon::now()->subDays(7);

        foreach ($pdfFiles as $file) {
            if (Storage::disk('public')->lastModified($file) < $sevenDaysAgo->getTimestamp()) {
                Storage::disk('public')->delete($file);
                $deletedPdfs++;
            }
        }
        $this->info("Cleanup complete for cached PDFs. Deleted {$deletedPdfs} old PDF(s).");
    }
}
