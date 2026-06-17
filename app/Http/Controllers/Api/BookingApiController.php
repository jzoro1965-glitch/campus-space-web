<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingApiController extends Controller
{
    const JAM_BUKA      = '07:00';
    const JAM_TUTUP     = '21:00';
    const MAKS_DURASI   = 3;
    const MAKS_PER_HARI = 1;

    /**
     * GET /api/bookings
     * Riwayat semua booking milik user yang login
     */
    public function index(Request $request)
    {
        $bookings = Booking::with('desk')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time')
            ->get()
            ->map(fn ($b) => $this->formatBooking($b));

        return response()->json(['success' => true, 'data' => $bookings]);
    }

    /**
     * GET /api/bookings/{id}
     * Detail satu booking milik user
     */
    public function show(Request $request, $id)
    {
        $booking = Booking::with('desk')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $booking) {
            return response()->json(['success' => false, 'message' => 'Booking tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $this->formatBooking($booking)]);
    }

    /**
     * POST /api/bookings
     * Buat booking baru dengan validasi bisnis lengkap
     */
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

        // Validasi jam operasional
        if ($start < self::JAM_BUKA || $end > self::JAM_TUTUP) {
            return response()->json([
                'success' => false,
                'message' => 'Booking hanya tersedia antara jam ' . self::JAM_BUKA . ' – ' . self::JAM_TUTUP . ' WIB.',
            ], 422);
        }

        // Validasi durasi maksimal
        $durasiJam = (strtotime($end) - strtotime($start)) / 3600;
        if ($durasiJam > self::MAKS_DURASI) {
            return response()->json([
                'success' => false,
                'message' => 'Maksimal durasi booking adalah ' . self::MAKS_DURASI . ' jam.',
            ], 422);
        }

        // Validasi batas booking per hari
        $bookingHariIni = Booking::where('user_id', $request->user()->id)
            ->where('booking_date', $date)
            ->where('status', 'approved')
            ->count();

        if ($bookingHariIni >= self::MAKS_PER_HARI) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya bisa membuat ' . self::MAKS_PER_HARI . ' booking aktif per hari.',
            ], 422);
        }

        // Cek konflik jadwal
        $conflict = Booking::where('desk_id', $validated['desk_id'])
            ->where('booking_date', $date)
            ->where('status', 'approved')
            ->where('start_time', '<', $end . ':00')
            ->where('end_time',   '>', $start . ':00')
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Meja sudah dibooking pada rentang waktu tersebut.',
            ], 422);
        }

        $booking = Booking::create([
            'user_id'      => $request->user()->id,
            'desk_id'      => $validated['desk_id'],
            'booking_date' => $date,
            'start_time'   => $start . ':00',
            'end_time'     => $end . ':00',
            'status'       => 'approved',
        ]);

        $booking->load('desk');

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibuat.',
            'data'    => $this->formatBooking($booking),
        ], 201);
    }

    /**
     * DELETE /api/bookings/{id}
     * Batalkan booking milik sendiri
     */
    public function cancel(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $booking) {
            return response()->json(['success' => false, 'message' => 'Booking tidak ditemukan.'], 404);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Booking sudah dibatalkan.'], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json(['success' => true, 'message' => 'Booking berhasil dibatalkan.']);
    }

    /** Format data booking untuk response JSON */
    private function formatBooking(Booking $b): array
    {
        return [
            'id'            => $b->id,
            'desk_id'       => $b->desk_id,
            'desk_code'     => $b->desk->code ?? '-',
            'desk_location' => $b->desk->location ?? '-',
            'booking_date'  => $b->booking_date,
            'start_time'    => substr($b->start_time, 0, 5),
            'end_time'      => substr($b->end_time, 0, 5),
            'status'        => $b->status,
            'created_at'    => $b->created_at?->toISOString(),
        ];
    }
}
