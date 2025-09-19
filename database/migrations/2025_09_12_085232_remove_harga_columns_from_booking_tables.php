<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('total_harga');
        });

        Schema::table('booking_jamaahs', function (Blueprint $table) {
            $table->dropColumn('harga_paket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('total_harga', 10, 2)->after('total_jamaah');
        });

        Schema::table('booking_jamaahs', function (Blueprint $table) {
            $table->decimal('harga_paket', 10, 2)->after('hubungan_dengan_main');
        });
    }
};
