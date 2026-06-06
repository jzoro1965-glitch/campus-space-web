<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Desk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Halaman utama mahasiswa — tampilkan meja tersedia + form booking
     */
    public function index()
    {
        $today  = now()->format('Y-m-d');
        $desks  = Desk::all();

        // Ambil desk_id yang sudah ter-booking hari ini
        $bookedDeskIds = Booking::where('booking_date', $today)
            ->where('status', 'approved')
            ->pluck('desk_id');

        // Riwayat booking milik user ini
        $myBookings = Booking::with('desk')
            ->where('user_id', Auth::id())
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time')
            ->take(10)
            ->get();

        return view('mahasiswa.home', compact('desks', 'bookedDeskIds', 'myBookings', 'today'));
    }

    /**
     * POST /mahasiswa/bookings — simpan booking baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'desk_id'      => ['required', 'exists:desks,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        // Cek konflik jadwal
        $conflict = Booking::where('desk_id', $validated['desk_id'])
            ->where('booking_date', $validated['booking_date'])
            ->where('status', 'approved')
            ->where(function ($q) use ($validated) {
                $q->where('start_time', '<', $validated['end_time'] . ':00')
                  ->where('end_time', '>', $validated['start_time'] . ':00');
            })
            ->exists();

        if ($conflict) {
            return back()
                ->withInput()
                ->with('error', 'Meja sudah dibooking pada jam tersebut. Silakan pilih jam lain.');
        }

        Booking::create([
            'user_id'      => Auth::id(),
            'desk_id'      => $validated['desk_id'],
            'booking_date' => $validated['booking_date'],
            'start_time'   => $validated['start_time'] . ':00',
            'end_time'     => $validated['end_time'] . ':00',
            'status'       => 'approved',
        ]);

        return back()->with('success', 'Booking berhasil! Meja sudah terdaftar atas nama Anda.');
    }

    /**
     * DELETE /mahasiswa/bookings/{id} — batalkan booking milik sendiri
     */
    public function cancel(Booking $booking)
    {
        // Pastikan hanya bisa batalkan booking milik sendiri
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
