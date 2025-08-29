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
            $table->string('kode_booking')->unique();
            $table->string('kode_paket');
            $table->unsignedBigInteger('main_jamaah_id');
            $table->integer('total_jamaah');
            $table->decimal('total_harga', 15, 2);
            $table->enum('status', ['pending', 'confirmed', 'paid', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('kode_paket')->references('kode_paket')->on('packages')->onDelete('cascade');
            $table->foreign('main_jamaah_id')->references('id')->on('jamaahs')->onDelete('cascade');
            $table->index(['kode_booking', 'kode_paket']);
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
