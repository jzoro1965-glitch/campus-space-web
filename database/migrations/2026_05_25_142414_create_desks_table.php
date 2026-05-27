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
    Schema::create('desks', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // 'Meja A1', 'Meja A2'
        $table->string('location')->nullable(); //  'Lantai 1'
        $table->boolean('is_active')->default(true); // ngatur meja buka (true) / tutup (false) oleh admin
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desks');
    }
};
