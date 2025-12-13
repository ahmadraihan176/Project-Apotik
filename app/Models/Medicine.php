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
        'stock',
        'unit',
        'expired_date'
    ];

    protected $casts = [
        'expired_date' => 'date',
        'price' => 'decimal:2'
    ];

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function reduceStock($quantity)
    {
        $this->stock -= $quantity;
        $this->save();
    }
}