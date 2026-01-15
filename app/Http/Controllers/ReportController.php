<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\PenerimaanBarang;
use App\Models\PenerimaanBarangDetail;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function monthlyReport(Request $request)
    {
        // Ambil bulan dan tahun dari request, default bulan dan tahun sekarang
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Validasi bulan dan tahun
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        // Hitung pendapatan harian dalam satu bulan
        $pendapatanHarian = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('DATE(created_at) as tanggal, SUM(total_amount) as total_pendapatan, COUNT(*) as jumlah_transaksi')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Hitung total pendapatan bulanan
        $totalPendapatanBulanan = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('total_amount');

        // Hitung total transaksi bulanan
        $totalTransaksiBulanan = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        // Rekapan pembelian obat per bulan (detail dari PenerimaanBarang)
        $rekapanPembelian = PenerimaanBarang::with(['details.medicine', 'user'])
            ->whereYear('receipt_date', $year)
            ->whereMonth('receipt_date', $month)
            ->orderBy('receipt_date', 'desc')
            ->get();

        // Hitung total quantity pembelian per obat
        $totalQuantityPembelian = DB::table('penerimaan_barang_details')
            ->join('penerimaan_barang', 'penerimaan_barang_details.penerimaan_barang_id', '=', 'penerimaan_barang.id')
            ->join('medicines', 'penerimaan_barang_details.medicine_id', '=', 'medicines.id')
            ->whereYear('penerimaan_barang.receipt_date', $year)
            ->whereMonth('penerimaan_barang.receipt_date', $month)
            ->select(
                'medicines.id',
                'medicines.name',
                'medicines.unit',
                DB::raw('SUM(penerimaan_barang_details.quantity) as total_quantity'),
                DB::raw('SUM(penerimaan_barang_details.subtotal) as total_subtotal')
            )
            ->groupBy('medicines.id', 'medicines.name', 'medicines.unit')
            ->orderBy('total_quantity', 'desc')
            ->get();

        // Data untuk dropdown tahun (dinamis berdasarkan data transaksi)
        $years = getAvailableYears();

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $layout = getLayoutName();
        
        return view('admin.report.monthly', compact(
            'pendapatanHarian',
            'totalPendapatanBulanan',
            'totalTransaksiBulanan',
            'month',
            'year',
            'years',
            'months',
            'layout'
        ));
    }

    public function printMonthlyReport(Request $request)
    {
        // Ambil bulan dan tahun dari request, default bulan dan tahun sekarang
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Validasi bulan dan tahun
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        // Hitung pendapatan harian dalam satu bulan
        $pendapatanHarian = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('DATE(created_at) as tanggal, SUM(total_amount) as total_pendapatan, COUNT(*) as jumlah_transaksi')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Hitung total pendapatan bulanan
        $totalPendapatanBulanan = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('total_amount');

        // Hitung total transaksi bulanan
        $totalTransaksiBulanan = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        
        return view('admin.report.print-monthly', compact(
            'pendapatanHarian',
            'totalPendapatanBulanan',
            'totalTransaksiBulanan',
            'month',
            'year',
            'months'
        ));
    }

    public function rekapanPembelianObat(Request $request)
    {
        // Ambil bulan dan tahun dari request, default bulan dan tahun sekarang
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Validasi bulan dan tahun
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        // Hitung total pembelian per bulan dari penerimaan farmasi
        $totalPembelianBulanan = PenerimaanBarang::whereYear('receipt_date', $year)
            ->whereMonth('receipt_date', $month)
            ->sum('grand_total');

        // Hitung total quantity pembelian per obat dalam satu bulan
        $rekapanPembelianObat = DB::table('penerimaan_barang_details')
            ->join('penerimaan_barang', 'penerimaan_barang_details.penerimaan_barang_id', '=', 'penerimaan_barang.id')
            ->join('medicines', 'penerimaan_barang_details.medicine_id', '=', 'medicines.id')
            ->whereYear('penerimaan_barang.receipt_date', $year)
            ->whereMonth('penerimaan_barang.receipt_date', $month)
            ->select(
                'medicines.id',
                'medicines.name',
                'medicines.code',
                'medicines.unit',
                DB::raw('SUM(penerimaan_barang_details.quantity) as total_quantity'),
                DB::raw('SUM(penerimaan_barang_details.subtotal) as total_subtotal'),
                DB::raw('COUNT(DISTINCT penerimaan_barang.id) as jumlah_penerimaan')
            )
            ->groupBy('medicines.id', 'medicines.name', 'medicines.code', 'medicines.unit')
            ->orderBy('total_subtotal', 'desc')
            ->get();

        // Data untuk dropdown tahun (dinamis berdasarkan data transaksi)
        $years = getAvailableYears();

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $layout = getLayoutName();
        
        return view('admin.report.rekapan-pembelian-obat', compact(
            'totalPembelianBulanan',
            'rekapanPembelianObat',
            'month',
            'year',
            'years',
            'months',
            'layout'
        ));
    }

    public function labaRugi(Request $request)
    {
        // Ambil bulan dan tahun dari request, default bulan dan tahun sekarang
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Validasi bulan dan tahun
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        // Hitung pendapatan harian dalam satu bulan
        $pendapatanHarian = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('DATE(created_at) as tanggal, SUM(total_amount) as total_pendapatan, COUNT(*) as jumlah_transaksi')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Hitung total pendapatan bulanan
        $totalPendapatanBulanan = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('total_amount');

        // Hitung total transaksi bulanan
        $totalTransaksiBulanan = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        // Hitung HPP harian dan bulanan
        $labaRugiHarian = [];
        $totalHPPBulanan = 0;

        foreach ($pendapatanHarian as $hari) {
            $tanggal = $hari->tanggal;
            $pendapatanHari = $hari->total_pendapatan;
            
            // Ambil semua transaksi detail pada tanggal tersebut
            $transactionDetails = TransactionDetail::whereHas('transaction', function($query) use ($tanggal) {
                $query->whereDate('created_at', $tanggal);
            })->with(['medicine', 'transaction'])->get();

            $hppHari = 0;
            
            foreach ($transactionDetails as $detail) {
                $medicineId = $detail->medicine_id;
                $quantitySold = $detail->quantity;
                $hargaJualPerUnit = $detail->price; // Harga jual per unit dari transaction detail
                // Gunakan timestamp lengkap dari transaksi, bukan hanya tanggal
                $transactionDate = Carbon::parse($detail->transaction->created_at);

                // Hitung weighted average harga beli (HPP) per unit jual sampai tanggal transaksi
                // price di penerimaan_barang_details = harga beli per unit jual (setelah diskon + PPN, sebelum margin)
                $pembelianData = DB::table('penerimaan_barang_details')
                    ->join('penerimaan_barang', 'penerimaan_barang_details.penerimaan_barang_id', '=', 'penerimaan_barang.id')
                    ->where('penerimaan_barang_details.medicine_id', $medicineId)
                    ->where('penerimaan_barang.receipt_date', '<=', $transactionDate->format('Y-m-d'))
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
                    // Weighted average harga beli per unit jual
                    $avgHargaBeliPerUnitJual = $pembelianData->total_subtotal / $pembelianData->total_qty_unit_jual;
                    
                    // HPP untuk quantity yang dijual (jangan bulatkan per item, biarkan presisi penuh)
                    $hppItem = $avgHargaBeliPerUnitJual * $quantitySold;
                    $hppHari += $hppItem;
                } else {
                    // Jika tidak ada data pembelian, gunakan harga jual sebagai fallback (tidak ideal)
                    // Atau bisa diabaikan jika memang belum ada pembelian
                }
            }

            // Bulatkan HPP hanya di akhir setelah semua item dijumlahkan
            $hppHari = round($hppHari, 2);
            $labaRugiHari = round($pendapatanHari - $hppHari, 2);
            $totalHPPBulanan += $hppHari;

            $labaRugiHarian[] = [
                'tanggal' => $tanggal,
                'pendapatan' => $pendapatanHari,
                'hpp' => $hppHari,
                'laba_rugi' => $labaRugiHari,
                'jumlah_transaksi' => $hari->jumlah_transaksi,
                'persentase_laba' => $pendapatanHari > 0 ? ($labaRugiHari / $pendapatanHari) * 100 : 0
            ];
        }

        // Hitung total laba/rugi bulanan
        $totalLabaRugiBulanan = $totalPendapatanBulanan - $totalHPPBulanan;
        $persentaseLabaBulanan = $totalPendapatanBulanan > 0 ? ($totalLabaRugiBulanan / $totalPendapatanBulanan) * 100 : 0;

        // Data untuk dropdown tahun (dinamis berdasarkan data transaksi)
        $years = getAvailableYears();

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $layout = getLayoutName();
        
        return view('admin.report.laba-rugi', compact(
            'labaRugiHarian',
            'totalPendapatanBulanan',
            'totalHPPBulanan',
            'totalLabaRugiBulanan',
            'persentaseLabaBulanan',
            'totalTransaksiBulanan',
            'month',
            'year',
            'years',
            'months',
            'layout'
        ));
    }

    public function printLabaRugi(Request $request)
    {
        // Ambil bulan dan tahun dari request, default bulan dan tahun sekarang
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Validasi bulan dan tahun
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        // Hitung pendapatan harian dalam satu bulan
        $pendapatanHarian = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('DATE(created_at) as tanggal, SUM(total_amount) as total_pendapatan, COUNT(*) as jumlah_transaksi')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Hitung total pendapatan bulanan
        $totalPendapatanBulanan = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('total_amount');

        // Hitung total transaksi bulanan
        $totalTransaksiBulanan = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        // Hitung HPP harian dan bulanan
        $labaRugiHarian = [];
        $totalHPPBulanan = 0;

        foreach ($pendapatanHarian as $hari) {
            $tanggal = $hari->tanggal;
            $pendapatanHari = $hari->total_pendapatan;
            
            // Ambil semua transaksi detail pada tanggal tersebut
            $transactionDetails = TransactionDetail::whereHas('transaction', function($query) use ($tanggal) {
                $query->whereDate('created_at', $tanggal);
            })->with(['medicine', 'transaction'])->get();

            $hppHari = 0;
            
            foreach ($transactionDetails as $detail) {
                $medicineId = $detail->medicine_id;
                $quantitySold = $detail->quantity;
                $hargaJualPerUnit = $detail->price;
                // Gunakan timestamp lengkap dari transaksi, bukan hanya tanggal
                $transactionDate = Carbon::parse($detail->transaction->created_at);

                // Hitung weighted average harga beli (HPP) per unit jual sampai tanggal transaksi
                $pembelianData = DB::table('penerimaan_barang_details')
                    ->join('penerimaan_barang', 'penerimaan_barang_details.penerimaan_barang_id', '=', 'penerimaan_barang.id')
                    ->where('penerimaan_barang_details.medicine_id', $medicineId)
                    ->where('penerimaan_barang.receipt_date', '<=', $transactionDate->format('Y-m-d'))
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
                    // HPP untuk quantity yang dijual (jangan bulatkan per item, biarkan presisi penuh)
                    $hppItem = $avgHargaBeliPerUnitJual * $quantitySold;
                    $hppHari += $hppItem;
                }
            }

            // Bulatkan HPP hanya di akhir setelah semua item dijumlahkan
            $hppHari = round($hppHari, 2);
            $labaRugiHari = round($pendapatanHari - $hppHari, 2);
            $totalHPPBulanan += $hppHari;

            $labaRugiHarian[] = [
                'tanggal' => $tanggal,
                'pendapatan' => $pendapatanHari,
                'hpp' => $hppHari,
                'laba_rugi' => $labaRugiHari,
                'jumlah_transaksi' => $hari->jumlah_transaksi,
                'persentase_laba' => $pendapatanHari > 0 ? ($labaRugiHari / $pendapatanHari) * 100 : 0
            ];
        }

        // Hitung total laba/rugi bulanan
        $totalLabaRugiBulanan = $totalPendapatanBulanan - $totalHPPBulanan;
        $persentaseLabaBulanan = $totalPendapatanBulanan > 0 ? ($totalLabaRugiBulanan / $totalPendapatanBulanan) * 100 : 0;

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        
        return view('admin.report.print-laba-rugi', compact(
            'labaRugiHarian',
            'totalPendapatanBulanan',
            'totalHPPBulanan',
            'totalLabaRugiBulanan',
            'persentaseLabaBulanan',
            'totalTransaksiBulanan',
            'month',
            'year',
            'months'
        ));
    }
}
