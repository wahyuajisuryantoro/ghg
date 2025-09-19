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
       Schema::table('package_types', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('package_types', function (Blueprint $table) {
            $table->string('category')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_types', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('package_types', function (Blueprint $table) {
            $table->enum('category', ['Umroh', 'Haji'])->after('id');
        });
    }
};
