<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Desk;
use App\Models\Booking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    
        // 1. Membuat akun Admin untuk login di Web Dashboard
        $admin = User::create([
            'nim' => '11111111',
            'name' => 'Admin Study Space',
            'email' => 'admin@kampus.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Membuat akun Mahasiswa untuk testing booking via mobile
        $mahasiswa = User::create([
            'nim' => '22010001',
            'name' => 'Leovan Gamalia',
            'email' => 'leo@student.com',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
        ]);

        // 3. Membuat contoh data Meja (Slot) awal
        $meja1 = Desk::create(['code' => 'Meja A1', 'location' => 'Lantai 1']);
        $meja2 = Desk::create(['code' => 'Meja A2', 'location' => 'Lantai 1']);
        $meja3 = Desk::create(['code' => 'Meja A3', 'location' => 'Lantai 2']);

        // 4. Membuat simulasi Meja A1 yang sudah TER-BOOKING hari ini oleh mahasiswa
        Booking::create([
            'user_id' => $mahasiswa->id,
            'desk_id' => $meja1->id,
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'approved'
        ]);
    }
}
