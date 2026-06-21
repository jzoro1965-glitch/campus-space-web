<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorBooking extends Model
{
    protected $fillable = [
        'user_id', 'mentor_id', 'mentor_schedule_id',
        'order_id', 'amount', 'status', 'notes',
        'payment_type', 'midtrans_transaction_id',
        'snap_token', 'midtrans_response', 'paid_at',
    ];

    protected $casts = [
        'midtrans_response' => 'array',
        'paid_at'           => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mentor()
    {
        return $this->belongsTo(Mentor::class);
    }

    public function schedule()
    {
        return $this->belongsTo(MentorSchedule::class, 'mentor_schedule_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'paid', 'completed' => 'emerald',
            'pending'           => 'amber',
            'cancelled'         => 'red',
            'failed', 'expired' => 'orange',
            default             => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Menunggu Pembayaran',
            'paid'      => 'Dikonfirmasi',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'failed'    => 'Gagal',
            'expired'   => 'Kedaluwarsa',
            default     => ucfirst($this->status),
        };
    }
}
