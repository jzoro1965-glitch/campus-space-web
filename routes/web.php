<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeskController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MentorController;
use App\Http\Controllers\Mahasiswa\HomeController;
use App\Http\Controllers\Mahasiswa\MentorController as MahasiswaMentorController;
use App\Http\Controllers\MidtransController;

Route::get('/', fn () => view('auth.login'));

// ── ADMIN ──────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Meja
    Route::get('/desks',                 [DeskController::class, 'index'])->name('desks.index');
    Route::get('/desks/create',          [DeskController::class, 'create'])->name('desks.create');
    Route::post('/desks',                [DeskController::class, 'store'])->name('desks.store');
    Route::get('/desks/{desk}/edit',     [DeskController::class, 'edit'])->name('desks.edit');
    Route::put('/desks/{desk}',          [DeskController::class, 'update'])->name('desks.update');
    Route::patch('/desks/{desk}/toggle', [DeskController::class, 'toggleActive'])->name('desks.toggle');
    Route::delete('/desks/{desk}',       [DeskController::class, 'destroy'])->name('desks.destroy');

    // Booking meja
    Route::get('/bookings',                    [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create',             [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings',                   [BookingController::class, 'store'])->name('bookings.store');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::delete('/bookings/{booking}',       [BookingController::class, 'destroy'])->name('bookings.destroy');

    // User
    Route::get('/users',               [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create',        [UserController::class, 'create'])->name('users.create');
    Route::post('/users',              [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit',   [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}',        [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}',     [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');

    // ── Mentor ──────────────────────────────────────────────────────────
    Route::get('/mentors',                       [MentorController::class, 'index'])->name('mentors.index');
    Route::get('/mentors/create',                [MentorController::class, 'create'])->name('mentors.create');
    Route::post('/mentors',                      [MentorController::class, 'store'])->name('mentors.store');
    Route::get('/mentors/{mentor}/edit',         [MentorController::class, 'edit'])->name('mentors.edit');
    Route::put('/mentors/{mentor}',              [MentorController::class, 'update'])->name('mentors.update');
    Route::delete('/mentors/{mentor}',           [MentorController::class, 'destroy'])->name('mentors.destroy');

    // Jadwal mentor
    Route::get('/mentors/{mentor}/schedules',    [MentorController::class, 'schedules'])->name('mentors.schedules');
    Route::post('/mentors/{mentor}/schedules',   [MentorController::class, 'storeSchedule'])->name('mentors.schedules.store');
    Route::delete('/schedules/{schedule}',       [MentorController::class, 'destroySchedule'])->name('mentors.schedules.destroy');

    // Monitor booking mentor
    Route::get('/mentors-bookings',              [MentorController::class, 'bookings'])->name('mentors.bookings');
    Route::patch('/mentors-bookings/{booking}/cancel',   [MentorController::class, 'cancelBooking'])->name('mentors.bookings.cancel');
    Route::patch('/mentors-bookings/{booking}/complete', [MentorController::class, 'completeBooking'])->name('mentors.bookings.complete');
});

// ── MAHASISWA ──────────────────────────────────────────────────────────
Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['auth', 'role:mahasiswa'])->group(function () {

    // Booking meja (gratis)
    Route::get('/',                      [HomeController::class, 'index'])->name('home');
    Route::post('/bookings',             [HomeController::class, 'store'])->name('bookings.store');
    Route::delete('/bookings/{booking}', [HomeController::class, 'cancel'])->name('bookings.cancel');

    // ── Mentor (berbayar) ────────────────────────────────────────────────
    Route::get('/mentors',                              [MahasiswaMentorController::class, 'index'])->name('mentors.index');
    Route::get('/mentors/bookings',                     [MahasiswaMentorController::class, 'myBookings'])->name('mentors.my-bookings');
    Route::get('/mentors/{mentor}',                     [MahasiswaMentorController::class, 'show'])->name('mentors.show');
    Route::post('/mentors/{mentor}/book',               [MahasiswaMentorController::class, 'book'])->name('mentors.book');
    Route::get('/mentors/bookings/{booking}',           [MahasiswaMentorController::class, 'showBooking'])->name('mentors.booking.show');
    Route::delete('/mentors/bookings/{booking}/cancel', [MahasiswaMentorController::class, 'cancelBooking'])->name('mentors.booking.cancel');
});

// ── MIDTRANS WEBHOOK — no CSRF ─────────────────────────────────────────
Route::post('/payment/notification', [MidtransController::class, 'notification'])
    ->name('payment.notification')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/auth.php';
