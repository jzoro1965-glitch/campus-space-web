<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;


// 1. 
Route::get('/', function () {
    return view('welcome');
});

// 2. Rute Dashboard Admin Utama
Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('admin.dashboard');

// 3. Rute Otomatis  (Login, Register, Logout bawaan Breeze)
require __DIR__.'/auth.php';