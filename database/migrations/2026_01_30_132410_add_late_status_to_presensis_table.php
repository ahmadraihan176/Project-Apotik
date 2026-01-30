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
        Schema::table('presensis', function (Blueprint $table) {
            $table->string('status_kehadiran')->default('tepat_waktu')->after('jam_masuk'); // tepat_waktu, terlambat
            $table->integer('keterlambatan')->default(0)->after('status_kehadiran'); // dalam menit
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropColumn(['status_kehadiran', 'keterlambatan']);
        });
    }
};
