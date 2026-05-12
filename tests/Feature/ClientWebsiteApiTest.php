<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientWebsiteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_are_returned_with_their_websites(): void
    {
        $client = Client::create(['email' => 'client@example.com']);
        Website::create(['client_id' => $client->id, 'url' => 'https://example.com']);

        $response = $this->getJson('/api/clients');

        $response
            ->assertOk()
            ->assertJsonPath('clients.0.email', 'client@example.com')
            ->assertJsonPath('clients.0.websites.0.url', 'https://example.com');
    }
}
