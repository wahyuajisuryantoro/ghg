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
        Schema::table('packages', function (Blueprint $table) {
             $table->integer('total_booked')->default(0)->after('total_kursi');
            $table->integer('booked_ppiu')->default(0)->after('total_booked');
            $table->integer('booked_ghg')->default(0)->after('booked_ppiu');
            $table->integer('sisa_seat')->default(0)->after('booked_ghg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            //
        });
    }
};
