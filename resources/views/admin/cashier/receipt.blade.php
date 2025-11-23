@extends('layouts.admin')

@section('title', 'Struk Transaksi')
@section('header', 'Struk Transaksi')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-8" id="receipt">
        <div class="text-center mb-6 pb-6 border-b-2 border-gray-300">
            <h2 class="text-3xl font-bold text-sky-600">Langse Farma</h2>
            <p class="text-gray-600 mt-2">Jl. Kesehatan No. 123, Yogyakarta</p>
            <p class="text-gray-600">Telp: 0274-123456</p>
        </div>

        <div class="mb-6 space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-600">No. Transaksi:</span>
                <span class="font-semibold">{{ $transaction->transaction_code }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Tanggal:</span>
                <span class="font-semibold">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Petugas Penjualan:</span>
                <span class="font-semibold">{{ $transaction->user->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Metode Pembayaran:</span>
                <span class="font-semibold">{{ $transaction->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}</span>
            </div>
        </div>

        <table class="w-full mb-6">
            <thead>
                <tr class="border-b-2 border-gray-300">
                    <th class="text-left py-2">Item</th>
                    <th class="text-center py-2">Qty</th>
                    <th class="text-right py-2">Harga</th>
                    <th class="text-right py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->details as $detail)
                    <tr class="border-b border-gray-200">
                        <td class="py-2">{{ $detail->medicine->name }}</td>
                        <td class="text-center py-2">{{ $detail->quantity }}</td>
                        <td class="text-right py-2">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                        <td class="text-right py-2">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="space-y-2 mb-6 pt-4 border-t-2 border-gray-300">
            <div class="flex justify-between text-lg">
                <span class="font-semibold">Total:</span>
                <span class="font-bold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Bayar:</span>
                <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-lg">
                <span class="font-semibold">Kembalian:</span>
                <span class="font-bold text-green-600">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="text-center text-gray-600 text-sm border-t-2 border-gray-300 pt-4">
            <p>Terima kasih atas kunjungan Anda</p>
            <p>Semoga lekas sembuh!</p>
        </div>
    </div>

    <div class="mt-6 flex space-x-4">
        <button onclick="window.print()" class="flex-1 gradient-bg text-white font-semibold py-3 rounded-lg hover:opacity-90">
            <i class="fas fa-print mr-2"></i>Cetak Struk
        </button>
        <a href="{{ route('admin.cashier.index') }}" class="flex-1 text-center bg-gray-300 text-gray-700 font-semibold py-3 rounded-lg hover:bg-gray-400">
            <i class="fas fa-cart-shopping mr-2"></i>Transaksi Baru
        </a>
    </div>
</div>

@push('scripts')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #receipt, #receipt * {
        visibility: visible;
    }
    #receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
@endpush
@endsection