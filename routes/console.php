<?php

use App\Services\WebsiteMonitor;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('monitor:websites', function (WebsiteMonitor $monitor) {
    $count = $monitor->queueAll();

    $this->info("Queued {$count} website checks.");
})->purpose('Check monitored websites and alert clients when a site is down');

Schedule::command('monitor:websites')->everyFifteenMinutes()->withoutOverlapping();
