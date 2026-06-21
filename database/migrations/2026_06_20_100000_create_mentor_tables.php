<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tabel Mentor ──────────────────────────────────────────────────
        Schema::create('mentors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('expertise');           // Bidang keahlian: "Matematika, Fisika"
            $table->text('bio')->nullable();        // Deskripsi profil mentor
            $table->string('photo')->nullable();    // Path foto profil
            $table->unsignedInteger('price_per_session'); // Harga per sesi (Rupiah)
            $table->unsignedInteger('session_duration_minutes')->default(60); // Durasi default sesi
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Tabel Jadwal Mentor ───────────────────────────────────────────
        // Setiap baris = satu slot waktu yang mentor tersedia
        // Admin yang buat jadwal ini
        Schema::create('mentor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained()->onDelete('cascade');
            $table->date('date');                   // Tanggal tersedia
            $table->time('start_time');             // Jam mulai slot
            $table->time('end_time');               // Jam selesai slot
            $table->boolean('is_booked')->default(false); // Sudah di-booking atau belum
            $table->timestamps();

            // Satu mentor tidak bisa punya dua slot yang sama persis di hari yang sama
            $table->unique(['mentor_id', 'date', 'start_time']);
        });

        // ── Tabel Booking Sesi Mentor ─────────────────────────────────────
        Schema::create('mentor_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('mentor_id')->constrained()->onDelete('cascade');
            $table->foreignId('mentor_schedule_id')->constrained()->onDelete('cascade');

            // Data order & pembayaran
            $table->string('order_id')->unique();   // CS-MB-{userId}-{timestamp}
            $table->unsignedInteger('amount');      // Snapshot harga saat booking
            $table->string('status')->default('pending');
            // pending   = order dibuat, belum bayar
            // paid      = pembayaran sukses, sesi dikonfirmasi
            // failed    = pembayaran gagal/dibatalkan
            // expired   = batas waktu bayar habis
            // cancelled = dibatalkan mahasiswa/admin setelah paid
            // completed = sesi sudah berlangsung

            // Data dari Midtrans
            $table->string('payment_type')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('snap_token')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Catatan tambahan dari mahasiswa saat booking
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_bookings');
        Schema::dropIfExists('mentor_schedules');
        Schema::dropIfExists('mentors');
    }
};
