<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaanBarangDetail extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_barang_details';

    protected $fillable = [
        'penerimaan_barang_id',
        'medicine_id',
        'unit_kemasan',
        'isi_per_box',
        'unit_jual',
        'no_batch',
        'expired_date',
        'quantity',
        'price',
        'discount_percent',
        'discount_amount',
        'subtotal'
    ];

    protected $casts = [
        'expired_date' => 'date',
        'price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function penerimaanBarang(): BelongsTo
    {
        return $this->belongsTo(PenerimaanBarang::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
