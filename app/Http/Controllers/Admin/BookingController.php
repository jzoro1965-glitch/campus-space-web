<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'desk'])->orderByDesc('booking_date')->orderByDesc('start_time');

        // Filter opsional berdasarkan tanggal
        if ($request->filled('date')) {
            $query->where('booking_date', $request->date);
        }

        // Filter opsional berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(20)->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function cancel(Booking $booking)
    {
        $booking->update(['status' => 'cancelled']);

        return back()->with('success', "Booking #{$booking->id} berhasil dibatalkan.");
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return back()->with('success', 'Booking berhasil dihapus.');
    }
}
