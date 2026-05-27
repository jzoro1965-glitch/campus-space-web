<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desk extends Model
{
    // Kolom yang diizinkan untuk diisi massal
    protected $fillable = [
        'code', 
        'location', 
        'is_active'
    ];

    // Relasi: 1 Meja bisa punya banyak riwayat booking
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}