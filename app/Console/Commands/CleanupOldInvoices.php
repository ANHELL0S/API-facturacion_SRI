<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Comprobante;
use Carbon\Carbon;

class CleanupOldInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-old-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes PDF invoices and their associated barcodes older than 7 days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of old invoices and barcodes...');

        $sevenDaysAgo = Carbon::now()->subDays(7);
        $deletedCount = 0;

        // Find comprobantes older than 7 days
        $oldComprobantes = Comprobante::where('created_at', '<', $sevenDaysAgo)->get();

        foreach ($oldComprobantes as $comprobante) {
            // Construct PDF filename
            $pdfFileName = "invoices/FAC-{$comprobante->establecimiento}-{$comprobante->punto_emision}-{$comprobante->secuencial}.pdf";

            // Construct barcode filename
            $barcodeFileName = "barcodes/{$comprobante->clave_acceso}.png";

            // Delete PDF if it exists
            if (Storage::disk('public')->exists($pdfFileName)) {
                Storage::disk('public')->delete($pdfFileName);
                $this->line("Deleted PDF: {$pdfFileName}");
                $deletedCount++;
            }

            // Delete barcode if it exists
            if (Storage::disk('public')->exists($barcodeFileName)) {
                Storage::disk('public')->delete($barcodeFileName);
                $this->line("Deleted barcode: {$barcodeFileName}");
            }
        }

        $this->info("Cleanup complete. Deleted {$deletedCount} old invoice PDF(s) and their associated barcodes.");
    }
}
