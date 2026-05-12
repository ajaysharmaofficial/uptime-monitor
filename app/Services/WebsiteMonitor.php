<?php

namespace App\Services;

use App\Jobs\CheckWebsite;
use App\Mail\WebsiteDown;
use App\Models\Website;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Throwable;

class WebsiteMonitor
{
    public function queueAll(): int
    {
        Website::query()
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(500, function ($websites): void {
                foreach ($websites as $website) {
                    CheckWebsite::dispatch($website);
                }
            });

        return Website::count();
    }

    public function checkAll(): int
    {
        Website::query()
            ->with('client')
            ->orderBy('id')
            ->chunkById(100, function ($websites): void {
                foreach ($websites as $website) {
                    $this->check($website);
                }
            });

        return Website::count();
    }

    public function check(Website $website): void
    {
        $statusCode = null;
        $error = null;

        try {
            $response = Http::timeout(10)->get($website->url);
            $statusCode = $response->status();
            $isUp = $response->successful();
        } catch (ConnectionException $exception) {
            $isUp = false;
            $error = $exception->getMessage();
        } catch (Throwable $exception) {
            $isUp = false;
            $error = $exception->getMessage();
        }

        $website->forceFill([
            'last_status' => $isUp ? 'up' : 'down',
            'last_status_code' => $statusCode,
            'last_error' => $isUp ? null : $error,
            'last_checked_at' => now(),
        ])->save();

        if (! $isUp) {
            Mail::to($website->client->email)->queue(new WebsiteDown($website));

            $website->forceFill([
                'last_alert_sent_at' => now(),
            ])->save();
        }
    }
}
