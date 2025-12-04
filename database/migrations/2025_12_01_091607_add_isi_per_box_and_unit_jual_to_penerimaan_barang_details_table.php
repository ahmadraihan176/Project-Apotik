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
        Schema::table('penerimaan_barang_details', function (Blueprint $table) {
            $table->integer('isi_per_box')->nullable()->after('unit_kemasan');
            $table->string('unit_jual')->nullable()->after('isi_per_box');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penerimaan_barang_details', function (Blueprint $table) {
            $table->dropColumn(['isi_per_box', 'unit_jual']);
        });
    }
};
