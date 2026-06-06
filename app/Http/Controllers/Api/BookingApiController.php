<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Desk;
use Illuminate\Http\Request;

class BookingApiController extends Controller
{
    /**
     * GET /api/bookings
     * Riwayat booking milik user yang sedang login
     */
    public function index(Request $request)
    {
        $bookings = Booking::with('desk')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time')
            ->get()
            ->map(function ($b) {
                return [
                    'id'           => $b->id,
                    'desk_code'    => $b->desk->code,
                    'desk_location'=> $b->desk->location,
                    'booking_date' => $b->booking_date,
                    'start_time'   => substr($b->start_time, 0, 5),
                    'end_time'     => substr($b->end_time, 0, 5),
                    'status'       => $b->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $bookings,
        ]);
    }

    /**
     * POST /api/bookings
     * Buat booking baru — cek konflik jadwal terlebih dahulu
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'desk_id'      => ['required', 'exists:desks,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        // Cek apakah meja sudah dibooking orang lain di waktu yang overlap
        $conflict = Booking::where('desk_id', $validated['desk_id'])
            ->where('booking_date', $validated['booking_date'])
            ->where('status', 'approved')
            ->where(function ($q) use ($validated) {
                $q->where(function ($inner) use ($validated) {
                    $inner->where('start_time', '<', $validated['end_time'] . ':00')
                          ->where('end_time', '>', $validated['start_time'] . ':00');
                });
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Meja sudah dibooking pada rentang waktu tersebut. Pilih jam lain.',
            ], 422);
        }

        $booking = Booking::create([
            'user_id'      => $request->user()->id,
            'desk_id'      => $validated['desk_id'],
            'booking_date' => $validated['booking_date'],
            'start_time'   => $validated['start_time'] . ':00',
            'end_time'     => $validated['end_time'] . ':00',
            'status'       => 'approved',
        ]);

        $booking->load('desk');

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibuat.',
            'data'    => [
                'id'           => $booking->id,
                'desk_code'    => $booking->desk->code,
                'desk_location'=> $booking->desk->location,
                'booking_date' => $booking->booking_date,
                'start_time'   => substr($booking->start_time, 0, 5),
                'end_time'     => substr($booking->end_time, 0, 5),
                'status'       => $booking->status,
            ],
        ], 201);
    }

    /**
     * DELETE /api/bookings/{id}
     * Batalkan booking — hanya boleh milik sendiri
     */
    public function cancel(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan.',
            ], 404);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Booking sudah dibatalkan sebelumnya.',
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibatalkan.',
        ]);
    }
}
