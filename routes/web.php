<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeskController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Mahasiswa\HomeController;

// ── Halaman awal → redirect ke login ──────────────────────────────────
Route::get('/', fn () => view('auth.login'));

// ── ADMIN ROUTES — hanya role admin ───────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRUD Meja
    Route::get('/desks',             [DeskController::class, 'index'])->name('desks.index');
    Route::get('/desks/create',      [DeskController::class, 'create'])->name('desks.create');
    Route::post('/desks',            [DeskController::class, 'store'])->name('desks.store');
    Route::get('/desks/{desk}/edit', [DeskController::class, 'edit'])->name('desks.edit');
    Route::put('/desks/{desk}',      [DeskController::class, 'update'])->name('desks.update');
    Route::delete('/desks/{desk}',   [DeskController::class, 'destroy'])->name('desks.destroy');

    // Kelola Booking
    Route::get('/bookings',                    [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create',             [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings',                   [BookingController::class, 'store'])->name('bookings.store');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::delete('/bookings/{booking}',       [BookingController::class, 'destroy'])->name('bookings.destroy');

    // Kelola User
    Route::get('/users',               [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create',        [UserController::class, 'create'])->name('users.create');
    Route::post('/users',              [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit',   [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}',        [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}',     [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
});

// ── MAHASISWA ROUTES — hanya role mahasiswa ────────────────────────────
Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['auth', 'role:mahasiswa'])->group(function () {
    Route::get('/',                      [HomeController::class, 'index'])->name('home');
    Route::post('/bookings',             [HomeController::class, 'store'])->name('bookings.store');
    Route::delete('/bookings/{booking}', [HomeController::class, 'cancel'])->name('bookings.cancel');
});

// ── AUTH ROUTES ────────────────────────────────────────────────────────
require __DIR__.'/auth.php';
