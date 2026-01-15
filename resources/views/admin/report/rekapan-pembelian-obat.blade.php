@extends('layouts.admin')

@section('title', 'Rekapan Pembelian Obat')
@section('header', 'Rekapan Pembelian Obat')

@section('content')
@php
    $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
@endphp

<!-- Filter Bulan dan Tahun -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-filter mr-2"></i>Filter Rekapan
    </h3>
    <form action="{{ route($routePrefix . '.report.rekapan-pembelian-obat') }}" method="GET" id="filterForm" class="flex gap-4 items-end">
        <div class="flex-1">
            <label class="block text-gray-700 font-semibold mb-2">Bulan</label>
            <select name="month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500" onchange="document.getElementById('filterForm').submit()">
                @foreach($months as $monthNum => $monthName)
                    <option value="{{ $monthNum }}" {{ $month == $monthNum ? 'selected' : '' }}>
                        {{ $monthName }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <label class="block text-gray-700 font-semibold mb-2">Tahun</label>
            <select name="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500" onchange="document.getElementById('filterForm').submit()">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<!-- Total Pembelian Bulanan -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-sm mb-1">Total Pembelian Obat</p>
            <p class="text-3xl font-bold text-blue-600">
                Rp {{ number_format($totalPembelianBulanan, 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $months[$month] }} {{ $year }}</p>
        </div>
        <div class="bg-blue-100 p-4 rounded-full">
            <i class="fas fa-shopping-cart text-3xl text-blue-600"></i>
        </div>
    </div>
</div>

<!-- Rekapan Pembelian Obat per Bulan -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-pills mr-2"></i>Rekapan Total Pembelian Obat - {{ $months[$month] }} {{ $year }}
    </h3>
    <p class="text-sm text-gray-600 mb-4">
        Data diambil dari penerimaan farmasi pada bulan {{ $months[$month] }} {{ $year }}
    </p>
    
    @if($rekapanPembelianObat->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Obat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Obat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Penerimaan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pembelian</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($rekapanPembelianObat as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-gray-900">{{ $item->code ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-gray-900">{{ $item->name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full font-semibold">
                                    {{ $item->jumlah_penerimaan }}x
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-semibold">
                                    {{ number_format($item->total_quantity, 0, ',', '.') }} {{ $item->unit }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="font-semibold text-gray-900">
                                    Rp {{ number_format($item->total_subtotal, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-right font-semibold text-gray-700">
                            Total Keseluruhan:
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="font-semibold text-gray-900">
                                {{ number_format($rekapanPembelianObat->sum('total_quantity'), 0, ',', '.') }} unit
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-lg font-bold text-gray-900">
                                Rp {{ number_format($rekapanPembelianObat->sum('total_subtotal'), 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Statistik Tambahan -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Total Obat Dibeli</p>
                <p class="text-2xl font-bold text-blue-600">{{ $rekapanPembelianObat->count() }}</p>
                <p class="text-xs text-gray-500 mt-1">Jenis obat berbeda</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Total Quantity</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($rekapanPembelianObat->sum('total_quantity'), 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">Unit total dibeli</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Rata-rata per Obat</p>
                <p class="text-2xl font-bold text-purple-600">
                    Rp {{ number_format($rekapanPembelianObat->count() > 0 ? $rekapanPembelianObat->sum('total_subtotal') / $rekapanPembelianObat->count() : 0, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Rata-rata pembelian</p>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 text-lg">Tidak ada data pembelian obat pada bulan {{ $months[$month] }} {{ $year }}</p>
            <p class="text-gray-400 text-sm mt-2">Data diambil dari penerimaan farmasi</p>
        </div>
    @endif
</div>
@endsection
