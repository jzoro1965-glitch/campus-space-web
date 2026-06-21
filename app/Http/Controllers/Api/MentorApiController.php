<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use App\Models\MentorBooking;
use App\Models\MentorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class MentorApiController extends Controller
{
    public function __construct()
    {
        MidtransConfig::$serverKey    = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;
    }

    /**
     * GET /api/mentors?search=&expertise=
     */
    public function index(Request $request)
    {
        $query = Mentor::where('is_active', true);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('name', 'like', "%$s%")->orWhere('expertise', 'like', "%$s%")
            );
        }
        if ($request->filled('expertise')) {
            $query->where('expertise', 'like', '%' . $request->expertise . '%');
        }

        $mentors = $query->get()->map(fn($m) => $this->formatMentor($m));

        return response()->json(['success' => true, 'data' => $mentors]);
    }

    /**
     * GET /api/mentors/{id}
     * Detail mentor + jadwal tersedia
     */
    public function show($id)
    {
        $mentor = Mentor::where('is_active', true)->find($id);

        if (! $mentor) {
            return response()->json(['success' => false, 'message' => 'Mentor tidak ditemukan.'], 404);
        }

        $schedules = $mentor->availableSchedules()
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'date'       => $s->date->format('Y-m-d'),
                'day'        => $s->date->translatedFormat('l'),
                'start_time' => substr($s->start_time, 0, 5),
                'end_time'   => substr($s->end_time, 0, 5),
            ]);

        return response()->json([
            'success' => true,
            'data'    => array_merge($this->formatMentor($mentor), [
                'available_schedules' => $schedules,
            ]),
        ]);
    }

    /**
     * POST /api/mentor-bookings
     * Buat booking + ambil snap_token
     * Body: { "mentor_schedule_id": 1, "notes": "..." }
     */
    public function store(Request $request)
    {
        $request->validate([
            'mentor_schedule_id' => ['required', 'exists:mentor_schedules,id'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        // ── Bagian kritis: lock baris jadwal supaya request yang barengan
        //    tidak bisa dua-duanya lolos validasi is_booked. ────────────────
        try {
            [$booking, $schedule, $mentor] = DB::transaction(function () use ($request, $user) {
                $schedule = MentorSchedule::with('mentor')
                    ->where('id', $request->mentor_schedule_id)
                    ->lockForUpdate()
                    ->first();

                $mentor = $schedule?->mentor;

                if (! $mentor || ! $mentor->is_active) {
                    throw new \RuntimeException('Mentor tidak aktif.');
                }

                if ($schedule->is_booked) {
                    throw new \RuntimeException('Jadwal sudah dibooking orang lain.');
                }

                if ($schedule->date->lt(now()->toDateString())) {
                    throw new \RuntimeException('Jadwal sudah lewat.');
                }

                $alreadyBooked = MentorBooking::where('user_id', $user->id)
                    ->where('mentor_schedule_id', $schedule->id)
                    ->whereIn('status', ['pending', 'paid'])
                    ->exists();

                if ($alreadyBooked) {
                    throw new \RuntimeException('Anda sudah booking jadwal ini.');
                }

                $orderId = 'CS-MB-' . $user->id . '-' . time();

                // status tidak di-set di sini — kolom DB sudah default 'pending'
                $booking = MentorBooking::create([
                    'user_id'            => $user->id,
                    'mentor_id'          => $mentor->id,
                    'mentor_schedule_id' => $schedule->id,
                    'order_id'           => $orderId,
                    'amount'             => $mentor->price_per_session,
                    'notes'              => $request->notes,
                ]);

                $schedule->update(['is_booked' => true]);

                return [$booking, $schedule, $mentor];
            });
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        // ── Request Snap Token ke Midtrans — di luar transaction ───────────
        $params = [
            'transaction_details' => [
                'order_id'     => $booking->order_id,
                'gross_amount' => $booking->amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
            'item_details' => [[
                'id'       => 'MENTOR-' . $mentor->id,
                'price'    => $mentor->price_per_session,
                'quantity' => 1,
                'name'     => 'Sesi: ' . $mentor->name . ' ' . $schedule->date->format('d/m/Y'),
            ]],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $booking->update(['snap_token' => $snapToken]);

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibuat. Selesaikan pembayaran.',
                'data'    => [
                    'booking_id'  => $booking->id,
                    'order_id'    => $booking->order_id,
                    'amount'      => $mentor->price_per_session,
                    'snap_token'  => $snapToken,
                    'snap_url'    => 'https://app.' . (config('midtrans.is_production') ? '' : 'sandbox.') . 'midtrans.com/snap/v2/vtweb/' . $snapToken,
                ],
            ], 201);
        } catch (\Exception $e) {
            $booking->delete();
            $schedule->update(['is_booked' => false]);
            return response()->json(['success' => false, 'message' => 'Gagal terhubung payment gateway.'], 500);
        }
    }

    /**
     * GET /api/mentor-bookings
     * Riwayat booking mentor milik user
     */
    public function myBookings(Request $request)
    {
        $bookings = MentorBooking::with(['mentor', 'schedule'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($b) => $this->formatBooking($b));

        return response()->json(['success' => true, 'data' => $bookings]);
    }

    /**
     * GET /api/mentor-bookings/{id}
     */
    public function showBooking(Request $request, $id)
    {
        $booking = MentorBooking::with(['mentor', 'schedule'])
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $booking) {
            return response()->json(['success' => false, 'message' => 'Booking tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $this->formatBooking($booking)]);
    }

    /**
     * DELETE /api/mentor-bookings/{id}
     * Batalkan booking (hanya pending)
     */
    public function cancelBooking(Request $request, $id)
    {
        $booking = MentorBooking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $booking) {
            return response()->json(['success' => false, 'message' => 'Booking tidak ditemukan.'], 404);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya booking yang belum dibayar yang bisa dibatalkan.',
            ], 422);
        }

        $booking->status = 'cancelled';
        $booking->save();
        $booking->schedule()->update(['is_booked' => false]);

        return response()->json(['success' => true, 'message' => 'Booking berhasil dibatalkan.']);
    }

    private function formatMentor(Mentor $m): array
    {
        return [
            'id'                       => $m->id,
            'name'                     => $m->name,
            'email'                    => $m->email,
            'expertise'                => $m->expertise,
            'bio'                      => $m->bio,
            'price_per_session'        => $m->price_per_session,
            'price_label'              => $m->formatted_price,
            'session_duration_minutes' => $m->session_duration_minutes,
            'total_sessions'           => $m->total_bookings,
        ];
    }

    private function formatBooking(MentorBooking $b): array
    {
        return [
            'id'             => $b->id,
            'order_id'       => $b->order_id,
            'mentor_name'    => $b->mentor->name ?? '-',
            'mentor_expertise' => $b->mentor->expertise ?? '-',
            'schedule_date'  => $b->schedule?->date?->format('Y-m-d'),
            'schedule_start' => $b->schedule ? substr($b->schedule->start_time, 0, 5) : '-',
            'schedule_end'   => $b->schedule ? substr($b->schedule->end_time, 0, 5) : '-',
            'amount'         => $b->amount,
            'amount_label'   => $b->formatted_amount,
            'status'         => $b->status,
            'status_label'   => $b->status_label,
            'payment_type'   => $b->payment_type,
            'notes'          => $b->notes,
            'snap_token'     => $b->snap_token,
            'paid_at'        => $b->paid_at?->toISOString(),
            'created_at'     => $b->created_at?->toISOString(),
        ];
    }
}