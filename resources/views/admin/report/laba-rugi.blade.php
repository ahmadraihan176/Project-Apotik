@extends('layouts.admin')

@section('title', 'Laporan Laba Rugi')
@section('header', 'Laporan Laba Rugi')

@section('content')
@php
    $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
@endphp

<!-- Filter Bulan dan Tahun -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-filter mr-2"></i>Filter Laporan
        </h3>
        <a href="{{ route($routePrefix . '.report.laba-rugi.print', ['month' => $month, 'year' => $year]) }}" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-block">
            <i class="fas fa-print mr-2"></i>Cetak Laporan
        </a>
    </div>
    <form action="{{ route($routePrefix . '.report.laba-rugi') }}" method="GET" id="filterForm" class="flex gap-4 items-end">
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

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Pendapatan -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm mb-1 font-medium">Total Pendapatan</p>
                <p class="text-3xl font-bold">
                    Rp {{ number_format($totalPendapatanBulanan, 0, ',', '.') }}
                </p>
                <p class="text-blue-100 text-xs mt-2">{{ $months[$month] }} {{ $year }}</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas fa-arrow-up text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- HPP -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-md p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-orange-100 text-sm mb-1 font-medium">Total HPP</p>
                <p class="text-3xl font-bold">
                    Rp {{ number_format($totalHPPBulanan, 0, ',', '.') }}
                </p>
                <p class="text-orange-100 text-xs mt-2">Harga Pokok Penjualan</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas fa-box text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Laba/Rugi -->
    <div class="bg-gradient-to-r {{ $totalLabaRugiBulanan >= 0 ? 'from-green-500 to-green-600' : 'from-red-500 to-red-600' }} rounded-lg shadow-md p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="{{ $totalLabaRugiBulanan >= 0 ? 'text-green-100' : 'text-red-100' }} text-sm mb-1 font-medium">
                    {{ $totalLabaRugiBulanan >= 0 ? 'Laba' : 'Rugi' }}
                </p>
                <p class="text-3xl font-bold">
                    Rp {{ number_format(abs($totalLabaRugiBulanan), 0, ',', '.') }}
                </p>
                <p class="{{ $totalLabaRugiBulanan >= 0 ? 'text-green-100' : 'text-red-100' }} text-xs mt-2">
                    {{ number_format($persentaseLabaBulanan, 2) }}% dari pendapatan
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas {{ $totalLabaRugiBulanan >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Detail Harian -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-calendar-day mr-2"></i>Detail Laba/Rugi Harian - {{ $months[$month] }} {{ $year }}
        </h3>
    </div>

    @if(count($labaRugiHarian) > 0)
        <div class="overflow-x-auto border border-gray-300 rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">
                            No
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">
                            Tanggal
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">
                            Pendapatan
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">
                            HPP
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">
                            Laba/Rugi
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">
                            % Laba
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Transaksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($labaRugiHarian as $index => $hari)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                {{ \Carbon\Carbon::parse($hari['tanggal'])->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right border-r border-gray-200">
                                Rp {{ number_format($hari['pendapatan'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right border-r border-gray-200">
                                Rp {{ number_format($hari['hpp'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $hari['laba_rugi'] >= 0 ? 'text-green-600' : 'text-red-600' }} text-right border-r border-gray-200">
                                {{ $hari['laba_rugi'] >= 0 ? '+' : '' }}Rp {{ number_format($hari['laba_rugi'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $hari['laba_rugi'] >= 0 ? 'text-green-600' : 'text-red-600' }} text-right border-r border-gray-200">
                                {{ number_format($hari['persentase_laba'], 2) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $hari['jumlah_transaksi'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-right text-sm font-bold text-gray-900 border-t-2 border-gray-400 border-r border-gray-300">
                            TOTAL BULANAN:
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 border-t-2 border-gray-400 border-r border-gray-300">
                            Rp {{ number_format($totalPendapatanBulanan, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 border-t-2 border-gray-400 border-r border-gray-300">
                            Rp {{ number_format($totalHPPBulanan, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-bold {{ $totalLabaRugiBulanan >= 0 ? 'text-green-600' : 'text-red-600' }} border-t-2 border-gray-400 border-r border-gray-300">
                            {{ $totalLabaRugiBulanan >= 0 ? '+' : '' }}Rp {{ number_format(abs($totalLabaRugiBulanan), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-bold {{ $totalLabaRugiBulanan >= 0 ? 'text-green-600' : 'text-red-600' }} border-t-2 border-gray-400 border-r border-gray-300">
                            {{ number_format($persentaseLabaBulanan, 2) }}%
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-bold text-gray-900 border-t-2 border-gray-400">
                            {{ $totalTransaksiBulanan }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 text-lg">Tidak ada data laba/rugi pada bulan {{ $months[$month] }} {{ $year }}</p>
        </div>
    @endif
</div>
@endsection
