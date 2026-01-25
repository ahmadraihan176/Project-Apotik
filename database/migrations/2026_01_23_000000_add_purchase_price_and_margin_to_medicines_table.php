<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            // Untuk obat hasil import Excel yang tidak punya history penerimaan
            $table->decimal('purchase_price', 15, 2)->default(0)->after('price');
            $table->decimal('margin_percent', 5, 2)->default(0)->after('purchase_price');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'margin_percent']);
        });
    }
};

