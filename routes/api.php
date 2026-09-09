<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Public Routes:
| - POST /api/register or /api/auth/register
| - POST /api/login    or /api/auth/login
|
| Protected Routes (Sanctum Bearer Token required):
| - GET  /api/me       or /api/auth/me
| - POST /api/logout   or /api/auth/logout
|
*/

// ===================================================
// Public Auth Routes
// ===================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ===================================================
// Protected Auth Routes (Requires auth:sanctum)
// ===================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Grouped prefix option: /api/auth/*
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
