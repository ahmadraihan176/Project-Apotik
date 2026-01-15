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
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            $table->dropColumn(['materai', 'extra_discount_percent', 'extra_discount_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            $table->decimal('materai', 15, 2)->default(0)->after('ppn_amount');
            $table->decimal('extra_discount_percent', 5, 2)->default(0)->after('materai');
            $table->decimal('extra_discount_amount', 15, 2)->default(0)->after('extra_discount_percent');
        });
    }
};
