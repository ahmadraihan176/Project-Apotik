@extends('layouts.admin')

@section('title', 'Riwayat Transaksi')
@section('header', 'Riwayat Transaksi')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Filter Bulan dan Tahun -->
    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
        <form method="GET" action="{{ route(getRoutePrefix() . '.cashier.history') }}" id="filterForm" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <select name="month" id="month" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" onchange="document.getElementById('filterForm').submit()">
                    @foreach($months as $monthNum => $monthName)
                        <option value="{{ $monthNum }}" {{ $month == $monthNum ? 'selected' : '' }}>
                            {{ $monthName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                <select name="year" id="year" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" onchange="document.getElementById('filterForm').submit()">
                    @foreach($years as $yearOption)
                        <option value="{{ $yearOption }}" {{ $year == $yearOption ? 'selected' : '' }}>
                            {{ $yearOption }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($groupedTransactions->isEmpty())
        <div class="text-center py-12">
            <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 text-lg">Belum ada transaksi</p>
        </div>
    @else
        @foreach($groupedTransactions as $date => $transactions)
            @php
                $isFirst = $loop->first;
            @endphp
            <div class="mb-4 border border-gray-200 rounded-lg overflow-hidden">
                <button 
                    onclick="toggleDay('{{ $date }}')" 
                    class="w-full bg-gradient-to-r from-sky-300 via-sky-400 to-sky-500 text-white px-6 py-4 flex items-center justify-between hover:opacity-90 transition"
                >
                    <div class="flex items-center">
                        <i class="fas fa-calendar-day mr-3"></i>
                        <div class="text-left">
                            <h3 class="text-lg font-semibold">
                                {{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('D MMMM YYYY') }}
                            </h3>
                            <p class="text-sm opacity-90 mt-1">
                                Total Transaksi: {{ $transactions->count() }} | 
                                Total Penjualan: Rp {{ number_format($transactions->sum('total_amount'), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    <i id="icon-{{ $date }}" class="fas fa-chevron-{{ $isFirst ? 'down' : 'right' }} transition-transform"></i>
                </button>
                
                <div id="content-{{ $date }}" class="{{ $isFirst ? '' : 'hidden' }} overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Transaksi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-semibold text-gray-900">{{ $transaction->transaction_code }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $transaction->created_at->format('H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $transaction->user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <span class="px-2 py-1 rounded {{ $transaction->payment_method === 'qris' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $transaction->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-semibold text-green-600">
                                            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button onclick="showDetails('{{ $transaction->id }}')" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                                <tr id="detail-{{ $transaction->id }}" class="hidden bg-gray-50">
                                    <td colspan="6" class="px-6 py-4">
                                        <div class="space-y-2">
                                            <h4 class="font-semibold text-gray-800 mb-2">Detail Transaksi:</h4>
                                            @foreach($transaction->details as $detail)
                                                <div class="flex justify-between items-center p-2 bg-white rounded">
                                                    <div>
                                                        <span class="font-medium">{{ $detail->medicine->name }}</span>
                                                        <span class="text-gray-500 text-sm">x{{ $detail->quantity }}</span>
                                                    </div>
                                                    <span class="text-gray-700">
                                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                            <div class="border-t pt-2 mt-2 flex justify-between font-semibold">
                                                <span>Total:</span>
                                                <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Bayar:</span>
                                                <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between text-green-600">
                                                <span>Kembalian:</span>
                                                <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Metode Pembayaran:</span>
                                                <span class="font-semibold">{{ $transaction->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>

@push('scripts')
<script>
function showDetails(id) {
    const detailRow = document.getElementById('detail-' + id);
    detailRow.classList.toggle('hidden');
}

function toggleDay(date) {
    const content = document.getElementById('content-' + date);
    const icon = document.getElementById('icon-' + date);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
    }
}
</script>
@endpush
@endsection