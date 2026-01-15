@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Obat</p>
                <p class="text-3xl font-bold text-sky-600">{{ $totalMedicines }}</p>
            </div>
            <div class="bg-sky-100 p-4 rounded-full">
                <i class="fas fa-pills text-2xl text-sky-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Stok</p>
                <p class="text-3xl font-bold text-green-600">{{ $totalStock }}</p>
            </div>
            <div class="bg-green-100 p-4 rounded-full">
                <i class="fas fa-boxes text-2xl text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Transaksi Hari Ini</p>
                <p class="text-3xl font-bold text-purple-600">{{ $todayTransactions }}</p>
            </div>
            <div class="bg-purple-100 p-4 rounded-full">
                <i class="fas fa-receipt text-2xl text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Keuntungan Hari Ini</p>
                <p class="text-3xl font-bold {{ $todayProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $todayProfit >= 0 ? '+' : '' }}Rp {{ number_format(abs($todayProfit), 0, ',', '.') }}
                </p>
            </div>
            <div class="p-4 rounded-full {{ $todayProfit >= 0 ? 'bg-green-100' : 'bg-red-100' }}">
                <i class="fas fa-chart-line text-2xl {{ $todayProfit >= 0 ? 'text-green-600' : 'text-red-600' }}"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Low Stock Alert -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>Stok Menipis
        </h3>
        @if($lowStock->count() > 0)
            <div class="space-y-3">
                @foreach($lowStock as $medicine)
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $medicine->name }}</p>
                            <p class="text-sm text-gray-500">{{ $medicine->code }}</p>
                        </div>
                        <span class="px-3 py-1 bg-yellow-500 text-white rounded-full text-sm font-semibold">
                            {{ $medicine->stock }} {{ $medicine->unit }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">Tidak ada obat dengan stok menipis</p>
        @endif
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-clock text-sky-500 mr-2"></i>Transaksi Terakhir
        </h3>
        @if($recentTransactions->count() > 0)
            <div class="space-y-3">
                @foreach($recentTransactions as $transaction)
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $transaction->transaction_code }}</p>
                                <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d M Y H:i') }}</p>
                            </div>
                            <span class="text-green-600 font-semibold">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-600">
                            @foreach($transaction->details->take(2) as $detail)
                                <span class="inline-block bg-white px-2 py-1 rounded mr-1 mb-1">
                                    {{ $detail->medicine->name }} ({{ $detail->quantity }})
                                </span>
                            @endforeach
                            @if($transaction->details->count() > 2)
                                <span class="inline-block bg-white px-2 py-1 rounded">
                                    +{{ $transaction->details->count() - 2 }} lainnya
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">Belum ada transaksi</p>
        @endif
    </div>
</div>
@endsection


