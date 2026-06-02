<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabel Utama Meja (Desks)
        Schema::create('desks', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Contoh: A1, B3
            $table->string('location');     // Contoh: Lantai 1, Pojok
            $table->timestamps();
        });

        // Tabel Booking (Bookings)
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('desk_id')->constrained()->onDelete('cascade');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default('approved'); // approved, pending, rejected
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('desks');
    }
};