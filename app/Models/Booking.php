<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    // Kolom yang diizinkan untuk diisi massal
    protected $fillable = [
        'user_id', 
        'desk_id', 
        'booking_date', 
        'start_time', 
        'end_time', 
        'status'
    ];

    // Relasi balik: 1 data booking itu milik meja (Desk) yang mana
    public function desk()
    {
        return $this->belongsTo(Desk::class);
    }

    // Relasi balik: 1 data booking itu milik mahasiswa (User) siapa
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}