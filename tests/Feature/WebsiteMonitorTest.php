<?php

namespace Tests\Feature;

use App\Jobs\CheckWebsite;
use App\Mail\WebsiteDown;
use App\Models\Client;
use App\Models\Website;
use App\Services\WebsiteMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebsiteMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitor_command_queues_each_website_for_scalable_processing(): void
    {
        Queue::fake();

        $client = Client::create(['email' => 'client@example.com']);
        Website::create(['client_id' => $client->id, 'url' => 'https://example.com']);
        Website::create(['client_id' => $client->id, 'url' => 'https://laravel.com']);

        $this->artisan('monitor:websites')
            ->expectsOutput('Queued 2 website checks.')
            ->assertSuccessful();

        Queue::assertPushed(CheckWebsite::class, 2);
    }

    public function test_down_websites_are_marked_down_and_email_alerts_are_queued(): void
    {
        Mail::fake();
        Http::fake([
            'https://example.com' => Http::response('Server error', 500),
        ]);

        $client = Client::create(['email' => 'client@example.com']);
        $website = Website::create(['client_id' => $client->id, 'url' => 'https://example.com']);

        app(WebsiteMonitor::class)->check($website);

        $website->refresh();

        $this->assertSame('down', $website->last_status);
        $this->assertSame(500, $website->last_status_code);
        $this->assertNotNull($website->last_checked_at);
        $this->assertNotNull($website->last_alert_sent_at);

        Mail::assertQueued(WebsiteDown::class, function (WebsiteDown $mail) use ($website) {
            return $mail->hasTo('client@example.com')
                && $mail->website->is($website)
                && $mail->envelope()->subject === 'https://example.com is down!';
        });
    }

    public function test_down_email_renders_required_subject_body_and_sender(): void
    {
        $client = Client::create(['email' => 'client@example.com']);
        $website = Website::create(['client_id' => $client->id, 'url' => 'https://example.com']);
        $mail = new WebsiteDown($website);

        $this->assertSame('https://example.com is down!', $mail->envelope()->subject);
        $this->assertSame('do-not-reply@example.com', $mail->envelope()->from->address);
        $this->assertStringContainsString('https://example.com is down!', $mail->render());
    }

    public function test_successful_websites_are_marked_up_without_email_alerts(): void
    {
        Mail::fake();
        Http::fake([
            'https://example.com' => Http::response('OK', 200),
        ]);

        $client = Client::create(['email' => 'client@example.com']);
        $website = Website::create(['client_id' => $client->id, 'url' => 'https://example.com']);

        app(WebsiteMonitor::class)->check($website);

        $this->assertSame('up', $website->refresh()->last_status);
        Mail::assertNothingQueued();
    }
}
