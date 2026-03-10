<?php

use App\Http\Controllers\Api\ForwardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['api'])->group(function () {
    Route::post('/forward', [ForwardController::class, 'forward'])
        ->middleware(\App\Http\Middleware\VerifyApiKey::class);
    
    Route::get('/status/{requestId}', [ForwardController::class, 'status'])
        ->middleware(\App\Http\Middleware\VerifyApiKey::class);
});
