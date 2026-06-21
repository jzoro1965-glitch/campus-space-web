<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Desk;
use App\Models\User;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Daftar semua booking dengan filter
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'desk'])
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time');

        if ($request->filled('date')) {
            $query->where('booking_date', $request->input('date'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->whereHas('user', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('nim', 'like', "%{$s}%");
            });
        }

        $bookings = $query->paginate(20)->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Form buat booking atas nama mahasiswa
     */
    public function create()
    {
        $mahasiswas = User::where('role', 'mahasiswa')->orderBy('name')->get();
        // Admin hanya bisa pilih meja yang aktif saat buat booking manual
        $desks      = Desk::where('is_active', true)->orderBy('code')->get();
        $today      = now()->format('Y-m-d');

        return view('admin.bookings.create', compact('mahasiswas', 'desks', 'today'));
    }

    /**
     * Simpan booking baru yang dibuat admin atas nama mahasiswa
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => ['required', 'exists:users,id'],
            'desk_id'      => ['required', 'exists:desks,id'],
            'booking_date' => ['required', 'date'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        // Cek konflik jadwal
        $conflict = Booking::where('desk_id', $validated['desk_id'])
            ->where('booking_date', $validated['booking_date'])
            ->where('status', 'approved')
            ->where(function ($q) use ($validated) {
                $q->where('start_time', '<', $validated['end_time'] . ':00')
                  ->where('end_time',   '>', $validated['start_time'] . ':00');
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()
                ->with('error', 'Meja sudah dibooking pada rentang waktu tersebut. Pilih jam lain.');
        }

        $booking = Booking::create([
            'user_id'      => $validated['user_id'],
            'desk_id'      => $validated['desk_id'],
            'booking_date' => $validated['booking_date'],
            'start_time'   => $validated['start_time'] . ':00',
            'end_time'     => $validated['end_time'] . ':00',
            'status'       => 'approved',
        ]);

        $booking->load('user', 'desk');

        return redirect()->route('admin.bookings.index')
            ->with('success', "Booking meja {$booking->desk->code} atas nama {$booking->user->name} berhasil dibuat.");
    }

    /**
     * Batalkan booking (ubah status ke cancelled)
     */
    public function cancel(Booking $booking)
    {
        $booking->update(['status' => 'cancelled']);

        return back()->with('success', "Booking #{$booking->id} berhasil dibatalkan.");
    }

    /**
     * Hapus booking permanen
     */
    public function destroy(Booking $booking)
    {
        $booking->delete();

        return back()->with('success', 'Booking berhasil dihapus.');
    }
}
