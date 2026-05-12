<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $clients = [
            'ajays1.img@gmail.com' => [
                'https://laravel.com',
                'https://appsvisor.com',
                'https://example.com',
            ],
            'client@example.com' => [
                'https://github.com',
                'https://www.php.net',
            ],
        ];

        foreach ($clients as $email => $websites) {
            $client = Client::updateOrCreate(['email' => $email]);

            foreach ($websites as $url) {
                $client->websites()->updateOrCreate(['url' => $url]);
            }
        }
    }
}
