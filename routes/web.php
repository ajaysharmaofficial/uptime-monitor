<?php

use App\Http\Controllers\ClientWebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::get('/api/clients', [ClientWebsiteController::class, 'index']);
