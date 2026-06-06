<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeskController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Mahasiswa\HomeController;

// ── Halaman awal → redirect ke login ──────────────────────────────────
Route::get('/', function () {
    return view('auth.login');
});

// ── ADMIN ROUTES ───────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRUD Meja
    Route::get('/desks',              [DeskController::class, 'index'])->name('desks.index');
    Route::get('/desks/create',       [DeskController::class, 'create'])->name('desks.create');
    Route::post('/desks',             [DeskController::class, 'store'])->name('desks.store');
    Route::get('/desks/{desk}/edit',  [DeskController::class, 'edit'])->name('desks.edit');
    Route::put('/desks/{desk}',       [DeskController::class, 'update'])->name('desks.update');
    Route::delete('/desks/{desk}',    [DeskController::class, 'destroy'])->name('desks.destroy');

    // Kelola Booking
    Route::get('/bookings',                   [BookingController::class, 'index'])->name('bookings.index');
    Route::patch('/bookings/{booking}/cancel',[BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::delete('/bookings/{booking}',      [BookingController::class, 'destroy'])->name('bookings.destroy');
});

// ── MAHASISWA ROUTES ───────────────────────────────────────────────────
Route::prefix('mahasiswa')->name('mahasiswa.')->middleware('auth')->group(function () {
    Route::get('/',                          [HomeController::class, 'index'])->name('home');
    Route::post('/bookings',                 [HomeController::class, 'store'])->name('bookings.store');
    Route::delete('/bookings/{booking}',     [HomeController::class, 'cancel'])->name('bookings.cancel');
});

// ── AUTH ROUTES ────────────────────────────────────────────────────────
require __DIR__.'/auth.php';
