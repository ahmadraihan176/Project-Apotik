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
        Schema::create('penerimaan_barang', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_code')->unique();
            $table->date('receipt_date');
            $table->string('supplier_name')->nullable();
            $table->string('jenis_penerimaan')->nullable();
            $table->string('no_sp')->nullable();
            $table->string('no_faktur')->nullable();
            $table->enum('jenis_pembayaran', ['cash', 'tempo'])->default('cash');
            $table->date('jatuh_tempo')->nullable();
            $table->string('diterima_semua')->nullable();
            $table->string('no_urut')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('ppn_percent', 5, 2)->default(0);
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->decimal('materai', 15, 2)->default(0);
            $table->decimal('extra_discount_percent', 5, 2)->default(0);
            $table->decimal('extra_discount_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barang');
    }
};
