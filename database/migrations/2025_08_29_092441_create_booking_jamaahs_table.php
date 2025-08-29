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
        Schema::create('booking_jamaahs', function (Blueprint $table) {
              $table->id();
            $table->string('kode_booking');
            $table->unsignedBigInteger('jamaah_id');
            $table->boolean('is_main_jamaah')->default(false);
            $table->string('hubungan_dengan_main')->nullable();
            $table->decimal('harga_paket', 12, 2);
            $table->enum('status', ['active', 'inactive', 'cancelled'])->default('active');
            $table->timestamps();

            $table->foreign('kode_booking')->references('kode_booking')->on('bookings')->onDelete('cascade');
            $table->foreign('jamaah_id')->references('id')->on('jamaahs')->onDelete('cascade');

            $table->unique(['kode_booking', 'jamaah_id']);
            
            $table->index(['kode_booking']);
            $table->index(['jamaah_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_jamaahs');
    }
};
