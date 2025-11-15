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
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'paid_amount' => 'required|numeric|min:0'
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

            // Validasi pembayaran
            if ($request->paid_amount < $totalAmount) {
                return back()->with('error', 'Jumlah pembayaran kurang!');
            }

            // Buat transaksi
            $transaction = Transaction::create([
                'transaction_code' => Transaction::generateCode(),
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $request->paid_amount - $totalAmount,
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