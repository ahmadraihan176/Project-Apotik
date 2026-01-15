<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        // Validasi: Pastikan user yang login adalah karyawan
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();
        
        // Pastikan role adalah karyawan
        if ($user->role !== 'karyawan') {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Akses ditolak. Hanya karyawan yang dapat mengakses halaman ini.');
        }

        // Karyawan menggunakan dashboard umum yang sama dengan admin
        // Gunakan logika yang sama dengan DashboardController
        $totalMedicines = \App\Models\Medicine::count();
        $totalStock = \App\Models\Medicine::sum('stock');
        $lowStock = \App\Models\Medicine::where('stock', '<=', 10)->count();
        
        $todayTransactions = \App\Models\Transaction::whereDate('created_at', today())->count();
        $todayRevenue = \App\Models\Transaction::whereDate('created_at', today())->sum('total_amount');
        
        $todayProfit = 0;
        $todayTransactionDetails = \App\Models\TransactionDetail::whereHas('transaction', function($query) {
            $query->whereDate('created_at', today());
        })->with('medicine')->get();

        foreach ($todayTransactionDetails as $detail) {
            $medicineId = $detail->medicine_id;
            $quantitySold = $detail->quantity;
            $hargaJualPerUnit = $detail->price;
            $transactionDate = \Carbon\Carbon::parse($detail->transaction->created_at);

            $pembelianData = \Illuminate\Support\Facades\DB::table('penerimaan_barang_details')
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
            }
        }
        
        // Bulatkan total laba hanya di akhir setelah semua item dijumlahkan
        $todayProfit = round($todayProfit, 2);
        
        $monthTransactions = \App\Models\Transaction::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)->count();
        $monthRevenue = \App\Models\Transaction::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)->sum('total_amount');
        
        $monthProfit = 0;
        $monthTransactionDetails = \App\Models\TransactionDetail::whereHas('transaction', function($query) {
            $query->whereYear('created_at', now()->year)
                  ->whereMonth('created_at', now()->month);
        })->with('medicine')->get();

        foreach ($monthTransactionDetails as $detail) {
            $medicineId = $detail->medicine_id;
            $quantitySold = $detail->quantity;
            $hargaJualPerUnit = $detail->price;
            $transactionDate = \Carbon\Carbon::parse($detail->transaction->created_at);

            $pembelianData = \Illuminate\Support\Facades\DB::table('penerimaan_barang_details')
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
            }
        }
        
        // Bulatkan total laba hanya di akhir setelah semua item dijumlahkan
        $monthProfit = round($monthProfit, 2);
        
        $totalTransactions = \App\Models\Transaction::count();
        $totalRevenue = \App\Models\Transaction::sum('total_amount');
        $lowStockMedicines = \App\Models\Medicine::where('stock', '<=', 10)->get();
        $recentTransactions = \App\Models\Transaction::with('details.medicine', 'user')
            ->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalMedicines', 'totalStock', 'lowStock', 'todayTransactions', 'todayRevenue', 'todayProfit',
            'monthTransactions', 'monthRevenue', 'monthProfit', 'totalTransactions', 'totalRevenue',
            'lowStockMedicines', 'recentTransactions'
        ));
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->with('success', 'Anda telah logout.');
    }
}
