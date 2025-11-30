<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameDetail extends Model
{
    protected $fillable = [
        'stock_opname_id',
        'medicine_id',
        'batch_number',
        'expired_date',
        'condition',
        'system_stock',
        'physical_stock',
        'difference',
        'notes'
    ];

    protected $casts = [
        'expired_date' => 'date'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detail) {
            $detail->difference = $detail->physical_stock - $detail->system_stock;
        });
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function getConditionLabelAttribute(): string
    {
        return match($this->condition) {
            'baik' => 'Baik',
            'rusak' => 'Rusak',
            'kadaluarsa' => 'Kadaluarsa',
            'hampir_kadaluarsa' => 'Hampir Kadaluarsa',
            'retur' => 'Retur',
            default => 'Baik'
        };
    }
}
