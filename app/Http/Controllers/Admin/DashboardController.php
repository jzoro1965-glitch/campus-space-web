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
        // 1. Ambil semua data meja untuk ditampilkan di grid web admin
        $desks = Desk::all();
        $today = now()->format('Y-m-d');

        // 2. bla status meja khusus untuk tampilan Frontend Blade
        $deskStatus = $desks->map(function ($desk) use ($today) {
            $isBooked = Booking::where('desk_id', $desk->id)
                ->where('booking_date', $today)
                ->where('status', 'approved')
                ->exists();

            return [
                'id' => $desk->id,
                'code' => $desk->code,
                'location' => $desk->location,
                'status' => $isBooked ? 'Terisi' : 'Kosong',
                'bg_color' => $isBooked ? 'bg-amber-500' : 'bg-emerald-500', // Warna Tailwind untuk UI
            ];
        });

        // 3. Lempar data ke file frontend views/admin/dashboard.blade.php
        return view('admin.dashboard', compact('deskStatus'));
    }
}