<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mentor extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'expertise', 'bio',
        'photo', 'price_per_session', 'session_duration_minutes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function schedules()
    {
        return $this->hasMany(MentorSchedule::class);
    }

    public function bookings()
    {
        return $this->hasMany(MentorBooking::class);
    }

    /** Jadwal yang masih tersedia (belum di-booking, tanggal >= hari ini) */
    public function availableSchedules()
    {
        return $this->schedules()
            ->where('is_booked', false)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price_per_session, 0, ',', '.');
    }

    /** Rata-rata rating dari semua booking completed (placeholder untuk future feature) */
    public function getTotalBookingsAttribute(): int
    {
        return $this->bookings()->where('status', 'paid')->count()
             + $this->bookings()->where('status', 'completed')->count();
    }
}
