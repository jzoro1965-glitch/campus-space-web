<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Desk;
use App\Models\Mentor;
use App\Models\MentorBooking;
use App\Models\MentorSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ────────────────────────────────────────────────────
        User::create([
            'nim' => '11111111', 'name' => 'Admin Study Space',
            'email' => 'admin@kampus.com', 'password' => Hash::make('password'),
            'role' => 'admin', 'is_super_admin' => true,
        ]);

        // ── Mahasiswa ────────────────────────────────────────────────
        $mhs = [];
        foreach ([
            ['22010001','Leovan Gamalia','leo@student.com'],
            ['22010002','Siti Nurhaliza','siti@student.com'],
            ['22010003','Budi Santoso','budi@student.com'],
            ['22010004','Dewi Rahayu','dewi@student.com'],
            ['22010005','Raka Pratama','raka@student.com'],
        ] as [$nim, $name, $email]) {
            $mhs[] = User::create([
                'nim' => $nim, 'name' => $name, 'email' => $email,
                'password' => Hash::make('password'), 'role' => 'mahasiswa',
            ]);
        }
        [$mhs1,$mhs2,$mhs3,$mhs4,$mhs5] = $mhs;

        // ── Meja ─────────────────────────────────────────────────────
        $desks = [];
        foreach (['A','B','C','D'] as $floor => $letter) {
            foreach ([1,2,3] as $num) {
                $desks[$letter.$num] = Desk::create([
                    'code' => $letter.$num,
                    'location' => 'Lantai '.($floor+1),
                    'is_active' => true,
                ]);
            }
        }
        $desks['D3']->update(['is_active' => false]);

        // ── Mentor ───────────────────────────────────────────────────
        $mentorData = [
            [
                'name' => 'Dr. Ahmad Fauzi',
                'email' => 'ahmad.fauzi@mentor.com',
                'phone' => '08111111111',
                'expertise' => 'Matematika, Statistika',
                'bio' => 'Dosen matematika dengan 10 tahun pengalaman mengajar. Spesialis kalkulus dan statistika.',
                'price_per_session' => 75000,
                'session_duration_minutes' => 60,
            ],
            [
                'name' => 'Rina Kusumawati, M.Sc.',
                'email' => 'rina.k@mentor.com',
                'phone' => '08222222222',
                'expertise' => 'Pemrograman, Algoritma, Python',
                'bio' => 'Software engineer berpengalaman 8 tahun. Membantu mahasiswa memahami algoritma dan pemrograman.',
                'price_per_session' => 100000,
                'session_duration_minutes' => 60,
            ],
            [
                'name' => 'Bpk. Hendra Wijaya',
                'email' => 'hendra.w@mentor.com',
                'phone' => '08333333333',
                'expertise' => 'Fisika, Kimia',
                'bio' => 'Peneliti di bidang fisika terapan. Mengajar mahasiswa S1 selama 7 tahun.',
                'price_per_session' => 60000,
                'session_duration_minutes' => 90,
            ],
            [
                'name' => 'Sarah Amelia, S.E.',
                'email' => 'sarah.a@mentor.com',
                'phone' => '08444444444',
                'expertise' => 'Akuntansi, Manajemen Keuangan',
                'bio' => 'Konsultan keuangan dan dosen akuntansi. Berpengalaman membantu mahasiswa memahami laporan keuangan.',
                'price_per_session' => 80000,
                'session_duration_minutes' => 60,
            ],
        ];

        $mentors = [];
        foreach ($mentorData as $data) {
            $mentors[] = Mentor::create(array_merge($data, ['is_active' => true]));
        }
        [$mentor1, $mentor2, $mentor3, $mentor4] = $mentors;

        // ── Jadwal Mentor (7 hari ke depan) ─────────────────────────
        foreach ($mentors as $mentor) {
            for ($i = 1; $i <= 7; $i++) {
                $date = now()->addDays($i)->format('Y-m-d');
                // Slot pagi
                MentorSchedule::create([
                    'mentor_id' => $mentor->id,
                    'date' => $date,
                    'start_time' => '09:00:00',
                    'end_time' => $mentor->session_duration_minutes === 90 ? '10:30:00' : '10:00:00',
                    'is_booked' => false,
                ]);
                // Slot siang
                MentorSchedule::create([
                    'mentor_id' => $mentor->id,
                    'date' => $date,
                    'start_time' => '13:00:00',
                    'end_time' => $mentor->session_duration_minutes === 90 ? '14:30:00' : '14:00:00',
                    'is_booked' => false,
                ]);
            }
        }

        // ── Sample Booking Mentor (untuk demo) ───────────────────────
        // Booking yang sudah paid
        $schedule1 = $mentor1->schedules()->first();
        MentorBooking::create([
            'user_id' => $mhs1->id, 'mentor_id' => $mentor1->id,
            'mentor_schedule_id' => $schedule1->id,
            'order_id' => 'CS-MB-DEMO-001',
            'amount' => $mentor1->price_per_session,
            'status' => 'paid', 'payment_type' => 'bank_transfer',
            'paid_at' => now()->subHours(2),
            'notes' => 'Mau belajar integral dan turunan.',
        ]);
        $schedule1->update(['is_booked' => true]);

        // Booking pending (belum bayar)
        $schedule2 = $mentor2->schedules()->skip(1)->first();
        MentorBooking::create([
            'user_id' => $mhs2->id, 'mentor_id' => $mentor2->id,
            'mentor_schedule_id' => $schedule2->id,
            'order_id' => 'CS-MB-DEMO-002',
            'amount' => $mentor2->price_per_session,
            'status' => 'pending',
            'snap_token' => 'demo-snap-token-xxx',
            'notes' => 'Perlu bantuan debugging kode Python.',
        ]);
        $schedule2->update(['is_booked' => true]);

        // Booking completed (selesai)
        $schedule3 = $mentor3->schedules()->first();
        MentorBooking::create([
            'user_id' => $mhs3->id, 'mentor_id' => $mentor3->id,
            'mentor_schedule_id' => $schedule3->id,
            'order_id' => 'CS-MB-DEMO-003',
            'amount' => $mentor3->price_per_session,
            'status' => 'completed', 'payment_type' => 'gopay',
            'paid_at' => now()->subDays(1),
        ]);
        $schedule3->update(['is_booked' => true]);

        // ── Booking Meja ─────────────────────────────────────────────
        $today = now()->format('Y-m-d');
        $baseHour = max(8, (int) now()->format('H') + 1);

        if ($baseHour + 2 <= 21) {
            Booking::create(['user_id' => $mhs1->id, 'desk_id' => $desks['A1']->id,
                'booking_date' => $today, 'start_time' => sprintf('%02d:00:00', $baseHour),
                'end_time' => sprintf('%02d:00:00', min($baseHour+2, 21)), 'status' => 'approved']);
        }
        if ($baseHour + 2 <= 21) {
            Booking::create(['user_id' => $mhs2->id, 'desk_id' => $desks['B1']->id,
                'booking_date' => $today, 'start_time' => sprintf('%02d:00:00', $baseHour),
                'end_time' => sprintf('%02d:00:00', min($baseHour+2, 21)), 'status' => 'approved']);
        }

        $pastHour = max(7, (int) now()->format('H') - 3);
        Booking::create(['user_id' => $mhs3->id, 'desk_id' => $desks['C1']->id,
            'booking_date' => $today, 'start_time' => sprintf('%02d:00:00', $pastHour),
            'end_time' => sprintf('%02d:00:00', min($pastHour+2, 21)), 'status' => 'expired']);

        // Data historis booking meja untuk grafik
        $historyData = [
            6 => [[$mhs1,'A3','08:00','10:00'],[$mhs2,'B3','10:00','12:00'],[$mhs3,'C2','13:00','15:00']],
            5 => [[$mhs2,'A1','09:00','11:00'],[$mhs4,'D1','14:00','16:00']],
            4 => [[$mhs1,'B1','08:00','10:00'],[$mhs3,'C3','10:00','13:00'],[$mhs5,'A2','14:00','16:00']],
            3 => [[$mhs4,'A1','08:00','10:00'],[$mhs1,'B2','13:00','15:00']],
            2 => [[$mhs1,'B2','09:00','12:00'],[$mhs5,'C1','13:00','15:00'],[$mhs3,'A3','15:00','17:00']],
            1 => [[$mhs2,'A1','08:00','10:00'],[$mhs4,'B3','10:00','12:00'],[$mhs5,'D1','15:00','17:00']],
        ];
        foreach ($historyData as $daysAgo => $list) {
            $date = now()->subDays($daysAgo)->format('Y-m-d');
            foreach ($list as [$user, $code, $start, $end]) {
                Booking::create([
                    'user_id' => $user->id, 'desk_id' => $desks[$code]->id,
                    'booking_date' => $date,
                    'start_time' => $start.':00', 'end_time' => $end.':00',
                    'status' => 'approved',
                ]);
            }
        }
    }
}
