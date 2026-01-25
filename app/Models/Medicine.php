<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{

    protected $fillable = [
        'name',
        'code',
        'description',
        'price',
        'purchase_price',
        'margin_percent',
        'stock',
        'unit',
        'expired_date'
    ];

    protected $casts = [
        'expired_date' => 'date',
        'price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'margin_percent' => 'decimal:2',
    ];

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function penerimaanBarangDetails()
    {
        return $this->hasMany(PenerimaanBarangDetail::class);
    }

    public function reduceStock($quantity)
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity tidak boleh negatif');
        }
        
        if ($this->stock < $quantity) {
            throw new \Exception("Stok tidak mencukupi. Stok tersedia: {$this->stock}, dibutuhkan: {$quantity}");
        }
        
        $this->stock -= $quantity;
        $this->save();
    }

    public function addStock($quantity)
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity tidak boleh negatif');
        }
        
        $this->stock += $quantity;
        $this->save();
    }
}