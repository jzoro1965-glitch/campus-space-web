<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorSchedule extends Model
{
    protected $fillable = [
        'mentor_id', 'date', 'start_time', 'end_time', 'is_booked',
    ];

    protected $casts = [
        'is_booked' => 'boolean',
        'date'      => 'date',
    ];

    public function mentor()
    {
        return $this->belongsTo(Mentor::class);
    }

    public function booking()
    {
        return $this->hasOne(MentorBooking::class);
    }

    /** Format waktu: "Senin, 23 Jun 2026 · 09:00 – 10:00" */
    public function getFormattedSlotAttribute(): string
    {
        return $this->date->translatedFormat('l, d M Y')
            . ' · '
            . substr($this->start_time, 0, 5)
            . ' – '
            . substr($this->end_time, 0, 5);
    }
}
