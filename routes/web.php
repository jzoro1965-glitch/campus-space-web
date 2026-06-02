<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

// 1. Halaman Utama Welcome bawaan Laravel
Route::get('/', function () {
    return view('welcome');
});

// 2. Rute Dashboard Admin Utama (Tanpa middleware auth untuk keperluan testing developer)
Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

// 3. Muat file route eksternal auth.php agar layout tidak crash
require __DIR__.'/auth.php';