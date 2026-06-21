<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
|
| Tidak pakai middleware 'guest' di sini karena kalau session lama masih
| ada di DB (browser ditutup tanpa logout), middleware guest akan memblokir
| akses ke halaman login dan user tidak bisa login lagi.
|
| Solusinya: biarkan semua orang akses /login dan /register.
| Kalau user sudah login (session valid), controller yang handle redirect-nya.
|
*/

// Register
Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [RegisteredUserController::class, 'store']);

// Login — TANPA middleware guest
Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('login', [AuthenticatedSessionController::class, 'store']);

// Logout — hanya user yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
