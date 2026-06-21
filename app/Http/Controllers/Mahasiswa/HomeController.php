<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Desk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    const JAM_BUKA      = '07:00';
    const JAM_TUTUP     = '21:00';
    const MAKS_DURASI   = 3;
    const MAKS_PER_HARI = 1;

    public function index()
    {
        $today = now()->format('Y-m-d');
        $desks = Desk::where('is_active', true)->get();

        $bookedDeskIds = Booking::where('booking_date', $today)
            ->where('status', 'approved')
            ->pluck('desk_id');

        $myBookings = Booking::with('desk')
            ->where('user_id', Auth::id())
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time')
            ->take(15)
            ->get();

        $todayBookings = Booking::with(['user', 'desk'])
            ->where('booking_date', $today)
            ->where('status', 'approved')
            ->orderBy('start_time')
            ->get();

        $sudahBookingHariIni = Booking::where('user_id', Auth::id())
            ->where('booking_date', $today)
            ->where('status', 'approved')
            ->exists();

        return view('mahasiswa.home', compact(
            'desks', 'bookedDeskIds', 'myBookings',
            'today', 'sudahBookingHariIni', 'todayBookings'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'desk_id'      => ['required', 'exists:desks,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $start = $validated['start_time'];
        $end   = $validated['end_time'];
        $date  = $validated['booking_date'];

        // 0. Cek meja masih aktif
        $desk = Desk::find($validated['desk_id']);
        if (! $desk || ! $desk->is_active) {
            return back()->withInput()
                ->with('error', 'Meja tidak tersedia atau sedang dinonaktifkan oleh admin.');
        }

        // 1. Cek jam operasional
        if ($start < self::JAM_BUKA || $end > self::JAM_TUTUP) {
            return back()->withInput()
                ->with('error', 'Booking hanya tersedia antara jam ' . self::JAM_BUKA . ' – ' . self::JAM_TUTUP . ' WIB.');
        }

        // 2. Cek maksimal durasi
        $durasiJam = (strtotime($end) - strtotime($start)) / 3600;
        if ($durasiJam > self::MAKS_DURASI) {
            return back()->withInput()
                ->with('error', 'Maksimal durasi booking adalah ' . self::MAKS_DURASI . ' jam.');
        }

        // 3. Cek batas booking per hari
        $bookingHariIni = Booking::where('user_id', Auth::id())
            ->where('booking_date', $date)
            ->where('status', 'approved')
            ->count();

        if ($bookingHariIni >= self::MAKS_PER_HARI) {
            return back()->withInput()
                ->with('error', 'Anda hanya bisa membuat ' . self::MAKS_PER_HARI . ' booking aktif per hari.');
        }

        // 4. Cek konflik jadwal
        $conflict = Booking::where('desk_id', $validated['desk_id'])
            ->where('booking_date', $date)
            ->where('status', 'approved')
            ->where('start_time', '<', $end . ':00')
            ->where('end_time',   '>', $start . ':00')
            ->exists();

        if ($conflict) {
            return back()->withInput()
                ->with('error', 'Meja sudah dibooking pada rentang waktu tersebut. Pilih jam lain.');
        }

        Booking::create([
            'user_id'      => Auth::id(),
            'desk_id'      => $validated['desk_id'],
            'booking_date' => $date,
            'start_time'   => $start . ':00',
            'end_time'     => $end . ':00',
            'status'       => 'approved',
        ]);

        return back()->with('success', 'Booking berhasil! Meja sudah terdaftar atas nama Anda.');
    }

    public function cancel(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak berhak membatalkan booking ini.');
        }

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Booking ini sudah dibatalkan sebelumnya.');
        }

        $booking->update(['status' => 'cancelled']);
        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}
