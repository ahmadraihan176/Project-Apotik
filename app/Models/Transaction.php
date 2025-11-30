<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'payment_method',
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
        $todayTransactions = self::whereDate('created_at', now())->get();
        
        // Cari nomor urut terakhir untuk hari ini
        $maxNumber = 0;
        foreach ($todayTransactions as $transaction) {
            // Handle format baru: TRX-YYYYMMDD-XXX
            if (preg_match('/TRX-' . $date . '-(\d+)$/', $transaction->transaction_code, $matches)) {
                $number = (int)$matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
            // Handle format lama: TRXYYYYMMDDXXXX (untuk backward compatibility)
            elseif (preg_match('/TRX' . $date . '(\d{4})$/', $transaction->transaction_code, $matches)) {
                $number = (int)$matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }
        
        $nextNumber = $maxNumber + 1;
        return 'TRX-' . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}