<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        // Menghubungkan ke user_id di tabel users
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        // Menghubungkan ke desk_id di tabel desks
        $table->foreignId('desk_id')->constrained('desks')->onDelete('cascade');
        
        $table->date('booking_date');
        $table->time('start_time');
        $table->time('end_time');
        $table->enum('status', ['pending', 'approved', 'cancelled'])->default('approved'); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
