<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Desk;
use Illuminate\Http\Request;

class DeskApiController extends Controller
{
    /**
     * GET /api/desks?date=2026-06-17
     * Daftar semua meja + status ready/booked untuk tanggal tertentu
     */
    public function index(Request $request)
    {
        $date  = $request->input('date', now()->format('Y-m-d'));
        $desks = Desk::all();

        $bookedIds = Booking::where('booking_date', $date)
            ->where('status', 'approved')
            ->pluck('desk_id')
            ->toArray();

        $data = $desks->map(fn ($desk) => [
            'id'       => $desk->id,
            'code'     => $desk->code,
            'location' => $desk->location,
            'status'   => in_array($desk->id, $bookedIds) ? 'booked' : 'ready',
        ]);

        return response()->json([
            'success' => true,
            'date'    => $date,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/desks/{id}?date=2026-06-17
     * Detail 1 meja + slot waktu yang sudah terisi hari itu
     */
    public function show(Request $request, $id)
    {
        $desk = Desk::find($id);

        if (! $desk) {
            return response()->json(['success' => false, 'message' => 'Meja tidak ditemukan.'], 404);
        }

        $date = $request->input('date', now()->format('Y-m-d'));

        $bookedSlots = Booking::where('desk_id', $desk->id)
            ->where('booking_date', $date)
            ->where('status', 'approved')
            ->get(['start_time', 'end_time'])
            ->map(fn ($b) => [
                'start' => substr($b->start_time, 0, 5),
                'end'   => substr($b->end_time, 0, 5),
            ]);

        return response()->json([
            'success'      => true,
            'data'         => [
                'id'           => $desk->id,
                'code'         => $desk->code,
                'location'     => $desk->location,
                'date'         => $date,
                'booked_slots' => $bookedSlots,
                'available'    => $bookedSlots->isEmpty(),
            ],
        ]);
    }

    /**
     * GET /api/desks/available?date=2026-06-17&start=09:00&end=11:00
     * Cek meja yang masih tersedia untuk rentang waktu tertentu
     */
    public function available(Request $request)
    {
        $request->validate([
            'date'  => ['required', 'date'],
            'start' => ['required', 'date_format:H:i'],
            'end'   => ['required', 'date_format:H:i', 'after:start'],
        ]);

        $date  = $request->input('date');
        $start = $request->input('start') . ':00';
        $end   = $request->input('end') . ':00';

        // Meja yang sudah ter-booking di rentang itu
        $occupiedDeskIds = Booking::where('booking_date', $date)
            ->where('status', 'approved')
            ->where('start_time', '<', $end)
            ->where('end_time',   '>', $start)
            ->pluck('desk_id')
            ->toArray();

        $available = Desk::whereNotIn('id', $occupiedDeskIds)
            ->get()
            ->map(fn ($d) => [
                'id'       => $d->id,
                'code'     => $d->code,
                'location' => $d->location,
            ]);

        return response()->json([
            'success'   => true,
            'date'      => $date,
            'start'     => substr($start, 0, 5),
            'end'       => substr($end, 0, 5),
            'available' => $available,
            'count'     => $available->count(),
        ]);
    }
}
