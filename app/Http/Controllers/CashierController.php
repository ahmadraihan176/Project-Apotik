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
        return view('admin.cashier.index', compact('medicines'));
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
            foreach ($request->items as $item) {
                $medicine = Medicine::findOrFail($item['medicine_id']);
                
                if ($medicine->stock < $item['quantity']) {
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
                return back()->with('error', 'Konfirmasi pembayaran QRIS wajib dicentang sebelum melanjutkan!');
            }

            if ($paymentMethod === 'cash' && $paidAmount < $totalAmount) {
                return back()->with('error', 'Jumlah pembayaran tunai kurang!');
            }

            if ($paymentMethod === 'cash' && !isset($validated['paid_amount'])) {
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

            return redirect()->route('admin.cashier.receipt', $transaction->id)
                ->with('success', 'Transaksi berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function receipt($id)
    {
        $transaction = Transaction::with('details.medicine')->findOrFail($id);
        return view('admin.cashier.receipt', compact('transaction'));
    }

    public function history()
    {
        $transactions = Transaction::with('details.medicine', 'user')
            ->latest()
            ->paginate(15);
        return view('admin.cashier.history', compact('transactions'));
    }
}