<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('invoices:sync-status')->everyFiveMinutes();

// Schedule::command('app:cleanup-old-invoices')->daily();

Schedule::command('app:cleanup-generated-files')->daily();
