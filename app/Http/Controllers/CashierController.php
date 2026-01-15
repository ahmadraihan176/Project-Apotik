<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function index()
    {
        $medicines = Medicine::where('stock', '>', 0)->get();
        $layout = getLayoutName();
        return view('admin.cashier.index', compact('medicines', 'layout'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,qris',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_confirmed' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $items = [];

            // Validasi stok dan hitung total
            // Gunakan lockForUpdate untuk mencegah race condition
            foreach ($request->items as $item) {
                $medicine = Medicine::lockForUpdate()->findOrFail($item['medicine_id']);
                
                if ($medicine->stock < $item['quantity']) {
                    DB::rollBack();
                    return back()->with('error', "Stok {$medicine->name} tidak mencukupi!");
                }

                $subtotal = $medicine->price * $item['quantity'];
                $totalAmount += $subtotal;

                $items[] = [
                    'medicine' => $medicine,
                    'quantity' => $item['quantity'],
                    'price' => $medicine->price,
                    'subtotal' => $subtotal
                ];
            }

            $paymentMethod = $validated['payment_method'];
            $paymentConfirmed = $request->boolean('payment_confirmed');
            $paidAmount = $paymentMethod === 'qris'
                ? $totalAmount
                : ($validated['paid_amount'] ?? 0);

            if ($paymentMethod === 'qris' && !$paymentConfirmed) {
                DB::rollBack();
                return back()->with('error', 'Konfirmasi pembayaran QRIS wajib dicentang sebelum melanjutkan!');
            }

            if ($paymentMethod === 'cash' && $paidAmount < $totalAmount) {
                DB::rollBack();
                return back()->with('error', 'Jumlah pembayaran tunai kurang!');
            }

            if ($paymentMethod === 'cash' && !isset($validated['paid_amount'])) {
                DB::rollBack();
                return back()->with('error', 'Jumlah pembayaran tunai wajib diisi!');
            }

            $changeAmount = $paymentMethod === 'qris'
                ? 0
                : $paidAmount - $totalAmount;

            // Buat transaksi
            $transaction = Transaction::create([
                'transaction_code' => Transaction::generateCode(),
                'payment_method' => $paymentMethod,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'user_id' => auth()->id()
            ]);

            // Simpan detail dan kurangi stok
            foreach ($items as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'medicine_id' => $item['medicine']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal']
                ]);

                $item['medicine']->reduceStock($item['quantity']);
            }

            DB::commit();

            $prefix = getRoutePrefix();
            return redirect()->route($prefix . '.cashier.receipt', $transaction->id)
                ->with('success', 'Transaksi berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function receipt($id)
    {
        $transaction = Transaction::with('details.medicine')->findOrFail($id);
        $layout = getLayoutName();
        return view('admin.cashier.receipt', compact('transaction', 'layout'));
    }

    public function history(Request $request)
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

        // Ambil transaksi berdasarkan bulan dan tahun yang dipilih
        $transactions = Transaction::with('details.medicine', 'user')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest()
            ->get();
        
        // Kelompokkan transaksi berdasarkan hari
        $groupedTransactions = $transactions->groupBy(function($transaction) {
            return $transaction->created_at->format('Y-m-d');
        });

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
        return view('admin.cashier.history', compact('groupedTransactions', 'month', 'year', 'months', 'years', 'layout'));
    }
}