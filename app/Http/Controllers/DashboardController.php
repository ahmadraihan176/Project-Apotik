<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Dashboard umum - bisa diakses admin dan karyawan
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Informasi Umum Apotik
        $totalMedicines = Medicine::count();
        $totalStock = Medicine::sum('stock');
        $lowStock = Medicine::where('stock', '<=', 10)->count();
        
        // Statistik Hari Ini
        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $todayRevenue = Transaction::whereDate('created_at', today())->sum('total_amount');
        
        // Hitung keuntungan hari ini (laba = harga jual - HPP)
        $todayProfit = 0;
        $todayTransactionDetails = TransactionDetail::whereHas('transaction', function($query) {
            $query->whereDate('created_at', today());
        })->with('medicine')->get();

        foreach ($todayTransactionDetails as $detail) {
            $medicineId = $detail->medicine_id;
            $quantitySold = $detail->quantity;
            $hargaJualPerUnit = $detail->price;
            $transactionDate = Carbon::parse($detail->transaction->created_at);

            $pembelianData = DB::table('penerimaan_barang_details')
                ->join('penerimaan_barang', 'penerimaan_barang_details.penerimaan_barang_id', '=', 'penerimaan_barang.id')
                ->where('penerimaan_barang_details.medicine_id', $medicineId)
                ->whereDate('penerimaan_barang.receipt_date', '<=', $transactionDate)
                ->selectRaw('
                    SUM(penerimaan_barang_details.price * 
                        CASE 
                            WHEN penerimaan_barang_details.unit_kemasan = "box" AND penerimaan_barang_details.isi_per_box > 0 
                            THEN penerimaan_barang_details.quantity * penerimaan_barang_details.isi_per_box
                            ELSE penerimaan_barang_details.quantity 
                        END
                    ) as total_subtotal,
                    SUM(CASE 
                        WHEN penerimaan_barang_details.unit_kemasan = "box" AND penerimaan_barang_details.isi_per_box > 0 
                        THEN penerimaan_barang_details.quantity * penerimaan_barang_details.isi_per_box
                        ELSE penerimaan_barang_details.quantity 
                    END) as total_qty_unit_jual
                ')
                ->first();

            if ($pembelianData && $pembelianData->total_qty_unit_jual > 0) {
                $avgHargaBeliPerUnitJual = $pembelianData->total_subtotal / $pembelianData->total_qty_unit_jual;
                $hppItem = $avgHargaBeliPerUnitJual * $quantitySold;
                $labaItem = ($hargaJualPerUnit * $quantitySold) - $hppItem;
                // Jangan bulatkan per item, biarkan presisi penuh sampai akhir
                $todayProfit += $labaItem;
            } else {
                // Jika tidak ada data pembelian (obat diupload dari Excel tanpa history penerimaan)
                // Anggap HPP = 0, sehingga laba = harga jual
                $labaItem = $hargaJualPerUnit * $quantitySold;
                $todayProfit += $labaItem;
            }
        }
        
        // Bulatkan total laba hanya di akhir setelah semua item dijumlahkan
        $todayProfit = round($todayProfit, 2);
        
        // Statistik Bulan Ini
        $monthTransactions = Transaction::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $monthRevenue = Transaction::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');
        
        // Hitung keuntungan bulan ini
        $monthProfit = 0;
        $monthTransactionDetails = TransactionDetail::whereHas('transaction', function($query) {
            $query->whereYear('created_at', now()->year)
                  ->whereMonth('created_at', now()->month);
        })->with('medicine')->get();

        foreach ($monthTransactionDetails as $detail) {
            $medicineId = $detail->medicine_id;
            $quantitySold = $detail->quantity;
            $hargaJualPerUnit = $detail->price;
            $transactionDate = Carbon::parse($detail->transaction->created_at);

            $pembelianData = DB::table('penerimaan_barang_details')
                ->join('penerimaan_barang', 'penerimaan_barang_details.penerimaan_barang_id', '=', 'penerimaan_barang.id')
                ->where('penerimaan_barang_details.medicine_id', $medicineId)
                ->whereDate('penerimaan_barang.receipt_date', '<=', $transactionDate)
                ->selectRaw('
                    SUM(penerimaan_barang_details.price * 
                        CASE 
                            WHEN penerimaan_barang_details.unit_kemasan = "box" AND penerimaan_barang_details.isi_per_box > 0 
                            THEN penerimaan_barang_details.quantity * penerimaan_barang_details.isi_per_box
                            ELSE penerimaan_barang_details.quantity 
                        END
                    ) as total_subtotal,
                    SUM(CASE 
                        WHEN penerimaan_barang_details.unit_kemasan = "box" AND penerimaan_barang_details.isi_per_box > 0 
                        THEN penerimaan_barang_details.quantity * penerimaan_barang_details.isi_per_box
                        ELSE penerimaan_barang_details.quantity 
                    END) as total_qty_unit_jual
                ')
                ->first();

            if ($pembelianData && $pembelianData->total_qty_unit_jual > 0) {
                $avgHargaBeliPerUnitJual = $pembelianData->total_subtotal / $pembelianData->total_qty_unit_jual;
                $hppItem = $avgHargaBeliPerUnitJual * $quantitySold;
                $labaItem = ($hargaJualPerUnit * $quantitySold) - $hppItem;
                // Jangan bulatkan per item, biarkan presisi penuh sampai akhir
                $monthProfit += $labaItem;
            } else {
                // Jika tidak ada data pembelian (obat diupload dari Excel tanpa history penerimaan)
                // Anggap HPP = 0, sehingga laba = harga jual
                $labaItem = $hargaJualPerUnit * $quantitySold;
                $monthProfit += $labaItem;
            }
        }
        
        // Bulatkan total laba hanya di akhir setelah semua item dijumlahkan
        $monthProfit = round($monthProfit, 2);
        
        // Total transaksi semua waktu
        $totalTransactions = Transaction::count();
        $totalRevenue = Transaction::sum('total_amount');
        
        // Obat dengan stok menipis (detail)
        $lowStockMedicines = Medicine::where('stock', '<=', 10)->get();
        
        // Transaksi terakhir
        $recentTransactions = Transaction::with('details.medicine', 'user')
            ->latest()
            ->take(5)
            ->get();

        // Dashboard umum menggunakan layouts.admin
        return view('admin.dashboard', compact(
            'totalMedicines',
            'totalStock',
            'lowStock',
            'todayTransactions',
            'todayRevenue',
            'todayProfit',
            'monthTransactions',
            'monthRevenue',
            'monthProfit',
            'totalTransactions',
            'totalRevenue',
            'lowStockMedicines',
            'recentTransactions'
        ));
    }
}