<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DeskApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\MentorApiController;

// ── PUBLIC ──────────────────────────────────────────────────────────────
Route::post('/login',    [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

// ── PROTECTED ───────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth & Profile
    Route::post('/logout',  [AuthApiController::class, 'logout']);
    Route::get('/profile',  [AuthApiController::class, 'profile']);
    Route::put('/profile',  [AuthApiController::class, 'updateProfile']);

    // Desks
    Route::get('/desks',           [DeskApiController::class, 'index']);
    Route::get('/desks/available', [DeskApiController::class, 'available']);
    Route::get('/desks/{id}',      [DeskApiController::class, 'show']);

    // Desk Bookings (gratis)
    Route::get('/bookings',         [BookingApiController::class, 'index']);
    Route::post('/bookings',        [BookingApiController::class, 'store']);
    Route::get('/bookings/{id}',    [BookingApiController::class, 'show']);
    Route::delete('/bookings/{id}', [BookingApiController::class, 'cancel']);

    // ── Mentor (berbayar) ─────────────────────────────────────────────────
    Route::get('/mentors',                  [MentorApiController::class, 'index']);
    Route::get('/mentors/{id}',             [MentorApiController::class, 'show']);

    Route::get('/mentor-bookings',          [MentorApiController::class, 'myBookings']);
    Route::post('/mentor-bookings',         [MentorApiController::class, 'store']);
    Route::get('/mentor-bookings/{id}',     [MentorApiController::class, 'showBooking']);
    Route::delete('/mentor-bookings/{id}',  [MentorApiController::class, 'cancelBooking']);
});
