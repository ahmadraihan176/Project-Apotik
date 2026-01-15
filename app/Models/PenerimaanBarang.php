<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanBarang extends Model
{

    protected $table = 'penerimaan_barang';

    protected $fillable = [
        'receipt_code',
        'receipt_date',
        'supplier_name',
        'jenis_penerimaan',
        'no_sp',
        'no_faktur',
        'jenis_pembayaran',
        'jatuh_tempo',
        'status_pembayaran',
        'tanggal_bayar',
        'diterima_semua',
        'no_urut',
        'total',
        'discount_percent',
        'discount_amount',
        'ppn_percent',
        'ppn_amount',
        'grand_total',
        'user_id',
        'notes'
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'jatuh_tempo' => 'date',
        'tanggal_bayar' => 'date',
        'total' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'ppn_percent' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'grand_total' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PenerimaanBarangDetail::class);
    }

    public static function generateCode()
    {
        $date = now()->format('Ymd');
        $todayReceipts = self::whereDate('created_at', now())->get();
        
        $maxNumber = 0;
        foreach ($todayReceipts as $receipt) {
            if (preg_match('/RCV-' . $date . '-(\d+)$/', $receipt->receipt_code, $matches)) {
                $number = (int)$matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }
        
        $nextNumber = $maxNumber + 1;
        return 'RCV-' . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public static function generateNoUrut($receiptDate = null)
    {
        $date = $receiptDate ? \Carbon\Carbon::parse($receiptDate) : now();
        $month = $date->month;
        $year = $date->year;
        
        // Ambil nomor urut terakhir di bulan dan tahun yang sama
        $lastPenerimaan = self::whereYear('receipt_date', $year)
            ->whereMonth('receipt_date', $month)
            ->whereNotNull('no_urut')
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = 1;
        
        if ($lastPenerimaan && $lastPenerimaan->no_urut) {
            // Parse nomor urut terakhir (format: 001/2026)
            if (preg_match('/^(\d+)\/' . $year . '$/', $lastPenerimaan->no_urut, $matches)) {
                $nextNumber = (int)$matches[1] + 1;
            }
        }
        
        return str_pad($nextNumber, 3, '0', STR_PAD_LEFT) . '/' . $year;
    }
}
