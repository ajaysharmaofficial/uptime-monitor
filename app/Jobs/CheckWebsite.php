<?php

namespace App\Jobs;

use App\Models\Website;
use App\Services\WebsiteMonitor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckWebsite implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public Website $website)
    {
    }

    public function handle(WebsiteMonitor $monitor): void
    {
        $monitor->check($this->website->loadMissing('client'));
    }
}
