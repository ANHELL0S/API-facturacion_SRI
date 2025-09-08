<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Comprobante;
use App\Enums\EstadosComprobanteEnum;
use App\Services\SriComprobanteService;
use Illuminate\Support\Facades\Log;

class SyncInvoiceStatus extends Command
{
    protected $signature = 'invoices:sync-status';
    protected $description = 'Sync the status of pending invoices with the SRI';

    protected $sriService;

    public function __construct(SriComprobanteService $sriService)
    {
        parent::__construct();
        $this->sriService = $sriService;
    }

    public function handle()
    {
        $this->info('Starting invoice status sync...');
        $pendingStatuses = [
            EstadosComprobanteEnum::PENDIENTE->value,
            EstadosComprobanteEnum::PROCESANDO->value,
            EstadosComprobanteEnum::FIRMADO->value,
        ];

        $invoicesToSync = Comprobante::whereIn('estado', $pendingStatuses)->get();

        if ($invoicesToSync->isEmpty()) {
            $this->info('No pending invoices to sync.');
            return;
        }

        $this->info("Found {$invoicesToSync->count()} invoices to sync.");

        foreach ($invoicesToSync as $invoice) {
            $this->info("Syncing invoice with key: {$invoice->clave_acceso}");
            try {
                $sriResponse = $this->sriService->enviarComprobanteAutorizacion($invoice->clave_acceso, $invoice->ambiente);

                if ($sriResponse['success']) {
                    $autorizacion = $sriResponse['autorizacion'];
                    $invoice->estado = $autorizacion->estado;
                    $invoice->fecha_autorizacion = new \DateTime($autorizacion->fechaAutorizacion);
                    $invoice->error_message = null;
                    $invoice->error_code = null;
                } else {
                    // This case should be handled by SriException, but as a fallback:
                    $invoice->estado = EstadosComprobanteEnum::FALLIDO->value;
                    $invoice->error_message = 'Fallo la sincronización';
                }

                $invoice->save();
                $this->info(" -> Status updated to: {$invoice->estado}");

            } catch (\App\Exceptions\SriException $e) {
                $invoice->estado = EstadosComprobanteEnum::FALLIDO->value;
                $invoice->error_code = $e->getCode();
                $invoice->error_message = $e->getMessage();
                $invoice->save();
                $this->error(" -> Failed to sync invoice {$invoice->clave_acceso}: " . $e->getMessage());
            } catch (\Exception $e) {
                Log::error("General error syncing invoice {$invoice->clave_acceso}: " . $e->getMessage());
                $this->error(" -> An unexpected error occurred for invoice {$invoice->clave_acceso}.");
            }
        }

        $this->info('Invoice status sync finished.');
    }
}
