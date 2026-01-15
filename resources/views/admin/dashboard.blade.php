{{-- ADMIN DASHBOARD SELALU MENGGUNAKAN LAYOUT ADMIN --}}
{{-- Karyawan menggunakan karyawan.dashboard yang terpisah --}}
@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<!-- Informasi Umum Apotik -->
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">
        <i class="fas fa-building mr-2"></i>Informasi Umum Apotik
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Obat</p>
                    <p class="text-3xl font-bold text-sky-600">{{ $totalMedicines }}</p>
                    <p class="text-xs text-gray-400 mt-1">jenis obat</p>
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
                    <p class="text-3xl font-bold text-green-600">{{ number_format($totalStock, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">unit tersedia</p>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-boxes text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Stok Menipis</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $lowStock }}</p>
                    <p class="text-xs text-gray-400 mt-1">obat perlu restock</p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-full">
                    <i class="fas fa-exclamation-triangle text-2xl text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Transaksi</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">semua waktu</p>
                </div>
                <div class="bg-indigo-100 p-4 rounded-full">
                    <i class="fas fa-shopping-cart text-2xl text-indigo-600"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Hari Ini -->
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">
        <i class="fas fa-calendar-day mr-2"></i>Statistik Hari Ini
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Transaksi</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $todayTransactions }}</p>
                    <p class="text-xs text-gray-400 mt-1">transaksi hari ini</p>
                </div>
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas fa-receipt text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pendapatan</p>
                    <p class="text-3xl font-bold text-blue-600">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">total penjualan</p>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-money-bill-wave text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Keuntungan</p>
                    <p class="text-3xl font-bold {{ $todayProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $todayProfit >= 0 ? '+' : '' }}Rp {{ number_format(abs($todayProfit), 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">laba bersih hari ini</p>
                </div>
                <div class="p-4 rounded-full {{ $todayProfit >= 0 ? 'bg-green-100' : 'bg-red-100' }}">
                    <i class="fas fa-chart-line text-2xl {{ $todayProfit >= 0 ? 'text-green-600' : 'text-red-600' }}"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Bulan Ini -->
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">
        <i class="fas fa-calendar-alt mr-2"></i>Statistik Bulan Ini ({{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM YYYY') }})
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Transaksi</p>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($monthTransactions, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">transaksi bulan ini</p>
                </div>
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas fa-receipt text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pendapatan</p>
                    <p class="text-3xl font-bold text-blue-600">Rp {{ number_format($monthRevenue, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">total penjualan bulan ini</p>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-money-bill-wave text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Keuntungan</p>
                    <p class="text-3xl font-bold {{ $monthProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $monthProfit >= 0 ? '+' : '' }}Rp {{ number_format(abs($monthProfit), 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">laba bersih bulan ini</p>
                </div>
                <div class="p-4 rounded-full {{ $monthProfit >= 0 ? 'bg-green-100' : 'bg-red-100' }}">
                    <i class="fas fa-chart-line text-2xl {{ $monthProfit >= 0 ? 'text-green-600' : 'text-red-600' }}"></i>
                </div>
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
        @if($lowStockMedicines->count() > 0)
            <div class="space-y-3">
                @foreach($lowStockMedicines as $medicine)
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