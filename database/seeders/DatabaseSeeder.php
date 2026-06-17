<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Desk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin ──────────────────────────────────────────────
        $admin = User::create([
            'nim'            => '11111111',
            'name'           => 'Admin Study Space',
            'email'          => 'admin@kampus.com',
            'password'       => Hash::make('password'),
            'role'           => 'admin',
            'is_super_admin' => true,
        ]);

        // ── Mahasiswa ────────────────────────────────────────────────
        $mhs1 = User::create(['nim' => '22010001', 'name' => 'Leovan Gamalia',  'email' => 'leo@student.com',  'password' => Hash::make('password'), 'role' => 'mahasiswa']);
        $mhs2 = User::create(['nim' => '22010002', 'name' => 'Siti Nurhaliza', 'email' => 'siti@student.com', 'password' => Hash::make('password'), 'role' => 'mahasiswa']);
        $mhs3 = User::create(['nim' => '22010003', 'name' => 'Budi Santoso',   'email' => 'budi@student.com', 'password' => Hash::make('password'), 'role' => 'mahasiswa']);
        $mhs4 = User::create(['nim' => '22010004', 'name' => 'Dewi Rahayu',    'email' => 'dewi@student.com', 'password' => Hash::make('password'), 'role' => 'mahasiswa']);

        // ── Meja (12 meja, 4 lantai) ─────────────────────────────────
        $desks = [];
        foreach (['A', 'B', 'C', 'D'] as $floor => $letter) {
            foreach ([1, 2, 3] as $num) {
                $desks[$letter.$num] = Desk::create([
                    'code'     => $letter . $num,
                    'location' => 'Lantai ' . ($floor + 1),
                ]);
            }
        }

        // ── Sample Booking hari ini ───────────────────────────────────
        $today = now()->format('Y-m-d');

        Booking::create(['user_id' => $mhs1->id, 'desk_id' => $desks['A1']->id, 'booking_date' => $today, 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'status' => 'approved']);
        Booking::create(['user_id' => $mhs2->id, 'desk_id' => $desks['B1']->id, 'booking_date' => $today, 'start_time' => '09:00:00', 'end_time' => '11:00:00', 'status' => 'approved']);
        Booking::create(['user_id' => $mhs3->id, 'desk_id' => $desks['C1']->id, 'booking_date' => $today, 'start_time' => '13:00:00', 'end_time' => '15:00:00', 'status' => 'approved']);

        // Sample booking minggu lalu untuk grafik
        for ($i = 1; $i <= 6; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            Booking::create(['user_id' => $mhs4->id, 'desk_id' => $desks['D1']->id, 'booking_date' => $date, 'start_time' => '10:00:00', 'end_time' => '12:00:00', 'status' => 'approved']);
        }
    }
}
