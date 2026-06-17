<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DeskApiController;
use App\Http\Controllers\Api\BookingApiController;

/*
|--------------------------------------------------------------------------
| API Routes — Campus Space
| Semua route bebas CSRF. Protected route butuh header:
| Authorization: Bearer {token}
|--------------------------------------------------------------------------
*/

// ── PUBLIC ──────────────────────────────────────────────────────────────
Route::post('/login',    [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

// ── PROTECTED ───────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth & Profile
    Route::post('/logout',         [AuthApiController::class, 'logout']);
    Route::get('/profile',         [AuthApiController::class, 'profile']);
    Route::put('/profile',         [AuthApiController::class, 'updateProfile']);

    // Desks
    Route::get('/desks',               [DeskApiController::class, 'index']);      // list + status
    Route::get('/desks/available',     [DeskApiController::class, 'available']);  // cek tersedia per slot waktu
    Route::get('/desks/{id}',          [DeskApiController::class, 'show']);       // detail + slot terisi

    // Bookings
    Route::get('/bookings',            [BookingApiController::class, 'index']);   // riwayat saya
    Route::post('/bookings',           [BookingApiController::class, 'store']);   // buat booking
    Route::get('/bookings/{id}',       [BookingApiController::class, 'show']);    // detail booking
    Route::delete('/bookings/{id}',    [BookingApiController::class, 'cancel']);  // batalkan
});
