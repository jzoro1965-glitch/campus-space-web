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

        // 3. Membuat contoh data Meja (Slot) lebih banyak untuk testing
        $meja1 = Desk::create(['code' => 'A1', 'location' => 'Lantai 1']);
        $meja2 = Desk::create(['code' => 'A2', 'location' => 'Lantai 1']);
        $meja3 = Desk::create(['code' => 'A3', 'location' => 'Lantai 1']);
        $meja4 = Desk::create(['code' => 'B1', 'location' => 'Lantai 2']);
        $meja5 = Desk::create(['code' => 'B2', 'location' => 'Lantai 2']);
        $meja6 = Desk::create(['code' => 'B3', 'location' => 'Lantai 2']);
        $meja7 = Desk::create(['code' => 'C1', 'location' => 'Lantai 3']);
        $meja8 = Desk::create(['code' => 'C2', 'location' => 'Lantai 3']);
        $meja9 = Desk::create(['code' => 'C3', 'location' => 'Lantai 3']);
        $meja10 = Desk::create(['code' => 'D1', 'location' => 'Lantai 4']);
        $meja11 = Desk::create(['code' => 'D2', 'location' => 'Lantai 4']);
        $meja12 = Desk::create(['code' => 'D3', 'location' => 'Lantai 4']);

        // Tambah beberapa mahasiswa lagi
        $mahasiswa2 = User::create([
            'nim' => '22010002',
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@student.com',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
        ]);

        $mahasiswa3 = User::create([
            'nim' => '22010003',
            'name' => 'Budi Santoso',
            'email' => 'budi@student.com',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
        ]);

        // 4. Membuat simulasi beberapa meja yang sudah TER-BOOKING hari ini
        Booking::create([
            'user_id' => $mahasiswa->id,
            'desk_id' => $meja1->id,
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => 'approved'
        ]);

        Booking::create([
            'user_id' => $mahasiswa2->id,
            'desk_id' => $meja4->id,
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '11:30:00',
            'status' => 'approved'
        ]);

        Booking::create([
            'user_id' => $mahasiswa3->id,
            'desk_id' => $meja7->id,
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'status' => 'approved'
        ]);
    }
}
