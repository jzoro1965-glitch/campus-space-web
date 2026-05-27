<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Desk;
use App\Models\Booking;
use Illuminate\Http\Request;

class DeskApiController extends Controller
{
    public function index()
    {
        $desks = Desk::all();
        
        // 2. Ambil tanggal hari ini (Format: Y-m-d) otomatis sesuai waktu server
        $today = now()->format('Y-m-d');

        // 3. Cocokkan dengan data tabel booking hari ini untuk menentukan status warna (Hijau/Kuning)
        $data = $desks->map(function ($desk) use ($today) {
            
            // Cek langsung ke tabel bookings apakah desk_id ini ada yang booking hari ini
            $isBooked = Booking::where('desk_id', $desk->id)
                ->where('booking_date', $today)
                ->where('status', 'approved')
                ->exists();

            return [
                'id' => $desk->id,
                'code' => $desk->code,
                'location' => $desk->location,
                'status' => $isBooked ? 'booked' : 'ready', // 'booked' = Kuning (Mobile), 'ready' = Hijau (Mobile)
            ];
        });

        // 4. Kembalikan data dalam bentuk format JSON bersih
        return response()->json([
            'success' => true,
            'message' => 'Daftar status meja hari ini',
            'data' => $data
        ], 200);
    }
}