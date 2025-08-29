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
       Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('kode_paket')->unique();
            $table->unsignedBigInteger('tipe_paket_id');
            $table->integer('total_kursi');
            $table->unsignedBigInteger('airlines_id');
            $table->integer('jumlah_hari');
            $table->unsignedBigInteger('hotel_mekka');
            $table->unsignedBigInteger('hotel_medina');
            $table->unsignedBigInteger('hotel_jedda')->nullable();
            $table->unsignedBigInteger('keberangkatan_id');
            $table->unsignedBigInteger('kota_tujuan_id');
            $table->string('no_penerbangan');
            $table->date('tanggal_berangkat');
            $table->integer('kurs_tetap');
            $table->decimal('hargaber2', 10, 2);
            $table->decimal('hargaber3', 10, 2);
            $table->decimal('hargaber4', 10, 2);
            $table->decimal('hargabayi', 10, 2);
            $table->timestamps();

            $table->foreign('tipe_paket_id')->references('id')->on('package_types');
            $table->foreign('airlines_id')->references('idairlines')->on('airlines');
            $table->foreign('hotel_mekka')->references('id')->on('hotels');
            $table->foreign('hotel_medina')->references('id')->on('hotels');
            $table->foreign('hotel_jedda')->references('id')->on('hotels');
            $table->foreign('keberangkatan_id')->references('id')->on('kota_keberangkatan');
            $table->foreign('kota_tujuan_id')->references('id')->on('kota_tujuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
