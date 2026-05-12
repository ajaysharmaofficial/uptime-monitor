<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;

class ClientWebsiteController extends Controller
{
    public function index(): JsonResponse
    {
        $clients = Client::query()
            ->select(['id', 'email'])
            ->with(['websites' => fn ($query) => $query
                ->select(['id', 'client_id', 'url'])
                ->orderBy('url')
            ])
            ->orderBy('email')
            ->get();

        return response()->json([
            'clients' => $clients,
        ]);
    }
}
