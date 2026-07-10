<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// autenticação máquina-a-máquina para o worker data-sync (Sanctum)
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());

    // sincronização eventos -> administração (esta API é a fonte/source)
    Route::get('/sync', [SyncController::class, 'deltas']);
    Route::get('/events-sync', [SyncController::class, 'events']);
    Route::get('/participants-sync', [SyncController::class, 'participants']);
});
