<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('catalog:sync-api')
    ->dailyAt('02:00')
    ->onFailure(function () {
        Log::error('Sync diário de catálogo via API falhou.');
    });
