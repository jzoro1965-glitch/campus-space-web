<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use App\Models\MentorBooking;
use App\Models\MentorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class MentorController extends Controller
{
    public function __construct()
    {
        MidtransConfig::$serverKey    = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;
    }

    /**
     * GET /mahasiswa/mentors
     * Daftar semua mentor aktif + filter keahlian
     */
    public function index(Request $request)
    {
        $query = Mentor::where('is_active', true);

        if ($request->filled('expertise')) {
            $query->where('expertise', 'like', '%' . $request->expertise . '%');
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")
                ->orWhere('expertise', 'like', "%$s%"));
        }

        $mentors = $query->withCount([
            'bookings as total_sessions' => fn($q) => $q->whereIn('status', ['paid', 'completed']),
        ])->orderBy('name')->get();

        // Kumpulkan semua bidang keahlian unik untuk filter
        $expertiseList = Mentor::where('is_active', true)
            ->pluck('expertise')
            ->flatMap(fn($e) => array_map('trim', explode(',', $e)))
            ->unique()->sort()->values();

        return view('mahasiswa.mentors.index', compact('mentors', 'expertiseList'));
    }

    /**
     * GET /mahasiswa/mentors/{mentor}
     * Detail profil mentor + daftar jadwal tersedia
     */
    public function show(Mentor $mentor)
    {
        if (! $mentor->is_active) abort(404);

        $schedules = $mentor->availableSchedules()->get();

        // Kelompokkan jadwal per tanggal untuk tampilan lebih rapi
        $groupedSchedules = $schedules->groupBy(fn($s) => $s->date->format('Y-m-d'));

        return view('mahasiswa.mentors.show', compact('mentor', 'groupedSchedules'));
    }

    /**
     * POST /mahasiswa/mentors/{mentor}/book
     * Buat order booking + ambil snap token Midtrans
     */
    public function book(Request $request, Mentor $mentor)
    {
        if (! $mentor->is_active) {
            return back()->with('error', 'Mentor tidak tersedia.');
        }

        $request->validate([
            'mentor_schedule_id' => ['required', 'exists:mentor_schedules,id'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();

        // ── Bagian kritis: lock baris jadwal supaya request yang barengan
        //    (dua mahasiswa klik booking di slot yang sama nyaris bersamaan)
        //    harus antre, bukan dua-duanya lolos validasi is_booked. ───────
        try {
            [$booking, $schedule] = DB::transaction(function () use ($request, $mentor, $user) {
                $schedule = MentorSchedule::where('id', $request->mentor_schedule_id)
                    ->lockForUpdate()
                    ->first();

                if (! $schedule || $schedule->mentor_id !== $mentor->id) {
                    throw new \RuntimeException('Jadwal tidak valid.');
                }

                if ($schedule->is_booked) {
                    throw new \RuntimeException('Jadwal ini sudah dibooking oleh orang lain. Pilih jadwal lain.');
                }

                if ($schedule->date->lt(now()->toDateString())) {
                    throw new \RuntimeException('Jadwal ini sudah lewat.');
                }

                $alreadyBooked = MentorBooking::where('user_id', $user->id)
                    ->where('mentor_schedule_id', $schedule->id)
                    ->whereIn('status', ['pending', 'paid'])
                    ->exists();

                if ($alreadyBooked) {
                    throw new \RuntimeException('Anda sudah memiliki booking untuk jadwal ini.');
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

                // Lock slot sementara — masih dalam transaction yang sama,
                // baris schedule ini sudah ke-lock dari lockForUpdate() di atas
                $schedule->update(['is_booked' => true]);

                return [$booking, $schedule];
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        // ── Request Snap Token ke Midtrans — sengaja DI LUAR transaction.
        //    Memanggil API eksternal sambil menahan row lock database itu
        //    riskan (lock ketahan lama kalau Midtrans lambat respons). ─────
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
                'name'     => 'Sesi Mentoring: ' . $mentor->name . ' (' . $schedule->date->format('d M Y') . ' ' . substr($schedule->start_time, 0, 5) . ')',
            ]],
            'callbacks' => [
                'finish' => route('mahasiswa.mentors.my-bookings'),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $booking->update(['snap_token' => $snapToken]);

            return view('mahasiswa.mentors.checkout', compact('booking', 'mentor', 'schedule', 'snapToken'));
        } catch (\Exception $e) {
            // Rollback manual: transaction sebelumnya sudah commit duluan,
            // jadi pembatalan booking + pembebasan slot dilakukan manual di sini.
            $booking->delete();
            $schedule->update(['is_booked' => false]);
            return back()->with('error', 'Gagal terhubung ke payment gateway. Coba lagi. (' . $e->getMessage() . ')');
        }
    }

    /**
     * GET /mahasiswa/mentors/bookings
     * Riwayat semua booking mentor milik mahasiswa yang login
     */
    public function myBookings()
    {
        $bookings = MentorBooking::with(['mentor', 'schedule'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('mahasiswa.mentors.my-bookings', compact('bookings'));
    }

    /**
     * GET /mahasiswa/mentors/bookings/{booking}
     * Detail satu booking
     */
    public function showBooking(MentorBooking $booking)
    {
        if ($booking->user_id !== Auth::id()) abort(403);
        $booking->load(['mentor', 'schedule']);
        return view('mahasiswa.mentors.booking-show', compact('booking'));
    }

    /**
     * DELETE /mahasiswa/mentors/bookings/{booking}
     * Mahasiswa batalkan booking (hanya status pending)
     */
    public function cancelBooking(MentorBooking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return back()->with('error', 'Akses ditolak.');
        }

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Hanya booking yang belum dibayar yang bisa dibatalkan sendiri. Hubungi admin untuk pembatalan setelah pembayaran.');
        }

        $booking->status = 'cancelled';
        $booking->save();
        $booking->schedule()->update(['is_booked' => false]);

        return back()->with('success', 'Booking berhasil dibatalkan. Jadwal kembali tersedia.');
    }
}