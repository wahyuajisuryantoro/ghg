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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
           $table->string('hotelname');
            $table->unsignedBigInteger('idhotelcity');
            $table->text('hoteladdress')->nullable();
            $table->text('notes')->nullable();
            $table->integer('bintang')->nullable();
            $table->integer('jarak')->nullable();
            $table->decimal('hotellat', 10, 8)->nullable();
            $table->decimal('hotellong', 11, 8)->nullable();
            $table->timestamps();

            $table->foreign('idhotelcity')->references('id')->on('cities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
