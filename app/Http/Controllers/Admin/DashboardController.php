<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Desk;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');
        $desks = Desk::all();

        $activeBookings = Booking::with(['user', 'desk'])
            ->where('booking_date', $today)
            ->where('status', 'approved')
            ->orderBy('start_time')
            ->get();

        // Statistik 7 hari terakhir untuk grafik
        $weeklyStats = Booking::select(
                DB::raw('DATE(booking_date) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->where('booking_date', '>=', now()->subDays(6)->format('Y-m-d'))
            ->where('status', 'approved')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Isi hari yang kosong dengan 0
        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d/m');
            $chartData[]   = $weeklyStats->get($d)?->total ?? 0;
        }

        // Top 5 meja paling sering dibooking
        $topDesks = Booking::select('desk_id', DB::raw('COUNT(*) as total'))
            ->with('desk')
            ->where('status', 'approved')
            ->groupBy('desk_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $totalMahasiswa = User::where('role', 'mahasiswa')->count();
        $totalBookingBulanIni = Booking::whereYear('booking_date', now()->year)
            ->whereMonth('booking_date', now()->month)
            ->where('status', 'approved')
            ->count();

        return view('admin.dashboard', compact(
            'desks', 'activeBookings',
            'chartLabels', 'chartData',
            'topDesks', 'totalMahasiswa', 'totalBookingBulanIni'
        ));
    }
}
