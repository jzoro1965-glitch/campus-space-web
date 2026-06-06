<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DeskApiController;
use App\Http\Controllers\Api\BookingApiController;

/*
|--------------------------------------------------------------------------
| API Routes — Campus Space
|--------------------------------------------------------------------------
| Semua route di sini otomatis diawali dengan prefix /api
| dan dikecualikan dari CSRF (sehingga aman dipakai di Postman & mobile app)
|--------------------------------------------------------------------------
*/

// ── PUBLIC ROUTES (tidak butuh token) ──────────────────────────────────
Route::post('/login',    [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

// ── PROTECTED ROUTES (butuh header: Authorization: Bearer {token}) ─────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout',  [AuthApiController::class, 'logout']);
    Route::get('/profile',  [AuthApiController::class, 'profile']);

    // Desks — daftar meja + status ketersediaan hari ini
    Route::get('/desks',    [DeskApiController::class, 'index']);

    // Bookings
    Route::get('/bookings',          [BookingApiController::class, 'index']);   // riwayat booking saya
    Route::post('/bookings',         [BookingApiController::class, 'store']);   // buat booking baru
    Route::delete('/bookings/{id}',  [BookingApiController::class, 'cancel']);  // batalkan booking
});
