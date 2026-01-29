<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule Gmail Sync
\Illuminate\Support\Facades\Schedule::command('app:sync-gmail-transactions')->everyTenMinutes();
