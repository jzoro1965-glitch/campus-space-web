<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use App\Models\MentorBooking;
use App\Models\MentorSchedule;
use Illuminate\Http\Request;

class MentorController extends Controller
{
    // ── CRUD MENTOR ───────────────────────────────────────────────────────

    public function index()
    {
        $mentors = Mentor::withCount([
            'bookings as total_bookings' => fn($q) => $q->whereIn('status', ['paid', 'completed']),
            'schedules as available_slots' => fn($q) => $q->where('is_booked', false)->where('date', '>=', now()->toDateString()),
        ])->orderBy('name')->get();

        $stats = [
            'total_mentors'   => Mentor::count(),
            'active_mentors'  => Mentor::where('is_active', true)->count(),
            'total_bookings'  => MentorBooking::whereIn('status', ['paid', 'completed'])->count(),
            'total_revenue'   => MentorBooking::where('status', 'paid')->orWhere('status', 'completed')->sum('amount'),
            'pending_payment' => MentorBooking::where('status', 'pending')->count(),
        ];

        return view('admin.mentors.index', compact('mentors', 'stats'));
    }

    public function create()
    {
        return view('admin.mentors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:100'],
            'email'                    => ['required', 'email', 'unique:mentors,email'],
            'phone'                    => ['nullable', 'string', 'max:20'],
            'expertise'                => ['required', 'string', 'max:200'],
            'bio'                      => ['nullable', 'string', 'max:1000'],
            'price_per_session'        => ['required', 'integer', 'min:5000'],
            'session_duration_minutes' => ['required', 'integer', 'in:30,45,60,90,120'],
            'is_active'                => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        Mentor::create($data);

        return redirect()->route('admin.mentors.index')
            ->with('success', "Mentor \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function edit(Mentor $mentor)
    {
        return view('admin.mentors.edit', compact('mentor'));
    }

    public function update(Request $request, Mentor $mentor)
    {
        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:100'],
            'email'                    => ['required', 'email', 'unique:mentors,email,' . $mentor->id],
            'phone'                    => ['nullable', 'string', 'max:20'],
            'expertise'                => ['required', 'string', 'max:200'],
            'bio'                      => ['nullable', 'string', 'max:1000'],
            'price_per_session'        => ['required', 'integer', 'min:5000'],
            'session_duration_minutes' => ['required', 'integer', 'in:30,45,60,90,120'],
            'is_active'                => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $mentor->update($data);

        return redirect()->route('admin.mentors.index')
            ->with('success', "Profil {$mentor->name} berhasil diperbarui.");
    }

    public function destroy(Mentor $mentor)
    {
        if ($mentor->bookings()->whereIn('status', ['paid', 'pending'])->exists()) {
            return back()->with('error', 'Mentor tidak bisa dihapus karena masih ada booking aktif.');
        }
        $mentor->delete();
        return back()->with('success', "Mentor \"{$mentor->name}\" berhasil dihapus.");
    }

    // ── KELOLA JADWAL ─────────────────────────────────────────────────────

    public function schedules(Mentor $mentor)
    {
        $schedules = $mentor->schedules()
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(20);

        return view('admin.mentors.schedules', compact('mentor', 'schedules'));
    }

    public function storeSchedule(Request $request, Mentor $mentor)
    {
        $request->validate([
            'date'       => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'repeat'     => ['sometimes', 'in:none,daily,weekly'],
            'repeat_until' => ['required_unless:repeat,none', 'nullable', 'date', 'after:date'],
        ]);

        $slots   = [];
        $date    = \Carbon\Carbon::parse($request->date);
        $repeat  = $request->input('repeat', 'none');
        $until   = $repeat !== 'none' ? \Carbon\Carbon::parse($request->repeat_until) : $date;

        while ($date->lte($until)) {
            $slots[] = [
                'mentor_id'  => $mentor->id,
                'date'       => $date->format('Y-m-d'),
                'start_time' => $request->start_time . ':00',
                'end_time'   => $request->end_time . ':00',
                'is_booked'  => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if ($repeat === 'daily')  $date->addDay();
            elseif ($repeat === 'weekly') $date->addWeek();
            else break;
        }

        // Insert ignore duplikat (unique constraint on mentor_id, date, start_time)
        $inserted = 0;
        foreach ($slots as $slot) {
            $exists = MentorSchedule::where('mentor_id', $slot['mentor_id'])
                ->where('date', $slot['date'])
                ->where('start_time', $slot['start_time'])
                ->exists();
            if (! $exists) {
                MentorSchedule::create($slot);
                $inserted++;
            }
        }

        return back()->with('success', "{$inserted} jadwal berhasil ditambahkan.");
    }

    public function destroySchedule(MentorSchedule $schedule)
    {
        if ($schedule->is_booked) {
            return back()->with('error', 'Jadwal tidak bisa dihapus karena sudah di-booking mahasiswa.');
        }
        $schedule->delete();
        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    // ── MONITOR BOOKING ───────────────────────────────────────────────────

    public function bookings(Request $request)
    {
        $query = MentorBooking::with(['user', 'mentor', 'schedule'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('mentor_id')) {
            $query->where('mentor_id', $request->mentor_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%$s%")->orWhere('nim', 'like', "%$s%")
            );
        }

        $bookings = $query->paginate(20)->withQueryString();
        $mentors  = Mentor::orderBy('name')->get();

        return view('admin.mentors.bookings', compact('bookings', 'mentors'));
    }

    public function cancelBooking(MentorBooking $booking)
    {
        if (in_array($booking->status, ['cancelled', 'completed'])) {
            return back()->with('error', 'Booking tidak bisa dibatalkan.');
        }

        $booking->update(['status' => 'cancelled']);
        // Bebaskan slot jadwal kembali
        $booking->schedule()->update(['is_booked' => false]);

        return back()->with('success', "Booking #{$booking->order_id} berhasil dibatalkan.");
    }

    public function completeBooking(MentorBooking $booking)
    {
        if ($booking->status !== 'paid') {
            return back()->with('error', 'Hanya booking dengan status "paid" yang bisa diselesaikan.');
        }
        $booking->update(['status' => 'completed']);
        return back()->with('success', "Booking #{$booking->order_id} ditandai selesai.");
    }
}
