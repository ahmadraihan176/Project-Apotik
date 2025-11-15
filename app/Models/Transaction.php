<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'total_amount',
        'paid_amount',
        'change_amount',
        'user_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public static function generateCode()
    {
        $date = now()->format('Ymd');
        $last = self::whereDate('created_at', now())->latest()->first();
        $number = $last ? (int)substr($last->transaction_code, -4) + 1 : 1;
        return 'TRX' . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}