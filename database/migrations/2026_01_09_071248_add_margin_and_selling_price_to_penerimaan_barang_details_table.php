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
            $table->decimal('margin_percent', 5, 2)->nullable()->after('discount_amount');
            $table->decimal('selling_price', 15, 2)->nullable()->after('margin_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penerimaan_barang_details', function (Blueprint $table) {
            $table->dropColumn(['margin_percent', 'selling_price']);
        });
    }
};
