<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desk;
use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ambil seluruh data Desk
        $desks = Desk::all();
        
        // 2. Ambil data Booking hari ini yang di-load bersama relasi 'user' dan 'desk'
        $today = now()->format('Y-m-d');
        $bookings = Booking::with(['user', 'desk'])
            ->where('booking_date', $today)
            ->where('status', 'approved')
            ->get();

        // 3. Kirim kedua kumpulan data ke view admin.dashboard
        return view('admin.dashboard', [
            'desks' => $desks,
            'activeBookings' => $bookings
        ]);
    }
}