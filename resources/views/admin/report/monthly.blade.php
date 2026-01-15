@extends('layouts.admin')

@section('title', 'Laporan Pendapatan Bulanan')
@section('header', 'Laporan Pendapatan Bulanan')

@section('content')
@php
    $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
@endphp

<!-- Filter Bulan dan Tahun -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6 no-print">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-filter mr-2"></i>Filter Laporan
        </h3>
        <a href="{{ route($routePrefix . '.report.monthly.print', ['month' => $month, 'year' => $year]) }}" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-block">
            <i class="fas fa-print mr-2"></i>Cetak Laporan
        </a>
    </div>
    <form action="{{ route($routePrefix . '.report.monthly') }}" method="GET" id="filterForm" class="flex gap-4 items-end">
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

<!-- Total Pendapatan Bulanan -->
<div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-md p-6 mb-6 text-white no-print">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-green-100 text-sm mb-1 font-medium">Total Pendapatan Bulanan</p>
            <p class="text-4xl font-bold">
                Rp {{ number_format($totalPendapatanBulanan, 0, ',', '.') }}
            </p>
            <p class="text-green-100 text-sm mt-2">{{ $months[$month] }} {{ $year }} | {{ $totalTransaksiBulanan }} transaksi</p>
        </div>
        <div class="bg-white bg-opacity-20 p-4 rounded-full">
            <i class="fas fa-chart-line text-4xl"></i>
        </div>
    </div>
</div>

<!-- Tabel Pendapatan Harian -->
<div id="print-area">
    <!-- Header untuk Print -->
    <div class="print-header" style="display: none;">
        <h2>Laporan Pendapatan Bulanan</h2>
        <p><strong>Langse Farma</strong></p>
        <p>{{ $months[$month] }} {{ $year }}</p>
        <p>Total Pendapatan: Rp {{ number_format($totalPendapatanBulanan, 0, ',', '.') }} | Total Transaksi: {{ $totalTransaksiBulanan }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 no-print">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-calendar-day mr-2"></i>Pendapatan Harian - {{ $months[$month] }} {{ $year }}
            </h3>
        </div>

    @if($pendapatanHarian->count() > 0)
        <div class="overflow-x-auto border border-gray-300 rounded-lg shadow-md" id="print-content">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">
                            No
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">
                            Tanggal
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">
                            Jumlah Transaksi
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Pendapatan
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pendapatanHarian as $index => $hari)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                {{ \Carbon\Carbon::parse($hari->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                {{ $hari->jumlah_transaksi }} transaksi
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                Rp {{ number_format($hari->total_pendapatan, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right text-sm font-bold text-gray-900 border-t-2 border-gray-400 border-r border-gray-300">
                            TOTAL PENDAPATAN BULANAN:
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 border-t-2 border-gray-400">
                            Rp {{ number_format($totalPendapatanBulanan, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Statistik Tambahan -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 no-print">
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Total Hari dengan Transaksi</p>
                <p class="text-2xl font-bold text-blue-600">{{ $pendapatanHarian->count() }}</p>
                <p class="text-xs text-gray-500 mt-1">hari</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Rata-rata per Hari</p>
                <p class="text-2xl font-bold text-green-600">
                    Rp {{ number_format($pendapatanHarian->count() > 0 ? $totalPendapatanBulanan / $pendapatanHarian->count() : 0, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500 mt-1">pendapatan harian</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Rata-rata per Transaksi</p>
                <p class="text-2xl font-bold text-purple-600">
                    Rp {{ number_format($totalTransaksiBulanan > 0 ? $totalPendapatanBulanan / $totalTransaksiBulanan : 0, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500 mt-1">per transaksi</p>
            </div>
        </div>
    @else
        <div class="text-center py-12 no-print">
            <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 text-lg">Tidak ada data pendapatan pada bulan {{ $months[$month] }} {{ $year }}</p>
        </div>
    @endif
</div>

@push('styles')
<style>
    
    /* Styling untuk PRINT - hanya tabel laporan, tanpa UI web */
    @media print {
        @page {
            margin: 1.5cm;
            size: A4;
        }
        
        /* Sembunyikan SEMUA elemen web */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
            width: 100% !important;
            height: auto !important;
        }
        
        /* Sembunyikan sidebar, header, dan semua elemen layout */
        aside, header, nav, .flex, .no-print {
            display: none !important;
            visibility: hidden !important;
        }
        
        /* Sembunyikan semua elemen di body kecuali print-area */
        body > div:not(#print-area),
        body > *:not(#print-area) {
            display: none !important;
            visibility: hidden !important;
        }
        
        /* Tampilkan HANYA konten laporan */
        #print-area {
            display: block !important;
            visibility: visible !important;
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 20px !important;
            background: white !important;
            page-break-after: auto;
        }
        
        #print-area .no-print {
            display: none !important;
            visibility: hidden !important;
        }
        
        /* Header laporan untuk print */
        .print-header {
            display: block !important;
            visibility: visible !important;
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #000;
        }
        
        .print-header h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: bold;
            color: #000 !important;
        }
        
        .print-header p {
            margin: 5px 0;
            font-size: 14px;
            color: #000 !important;
        }
        
        /* Styling tabel untuk print - profesional dan rapi */
        .report-table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 20px 0 0 0 !important;
            font-size: 11px !important;
            border: 2px solid #000 !important;
        }
        
        .report-table thead {
            display: table-header-group !important;
        }
        
        .report-table tfoot {
            display: table-footer-group !important;
        }
        
        .report-table th,
        .report-table td {
            border: 1px solid #000 !important;
            padding: 10px 12px !important;
            text-align: left !important;
            color: #000 !important;
            background: white !important;
        }
        
        .report-table th {
            background-color: #d1d5db !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-weight: bold !important;
            font-size: 12px !important;
            text-transform: uppercase !important;
        }
        
        .report-table tbody tr {
            page-break-inside: avoid;
            background: white !important;
        }
        
        .report-table tbody tr:nth-child(even) {
            background-color: #f9fafb !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .report-table tfoot {
            background-color: #d1d5db !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-weight: bold !important;
        }
        
        .report-table tfoot td {
            background-color: #d1d5db !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-size: 13px !important;
        }
        
        .report-table .text-right {
            text-align: right !important;
        }
        
        /* Hapus semua styling web */
        * {
            box-shadow: none !important;
            background-image: none !important;
            text-shadow: none !important;
        }
    }
</style>
@endpush
@endsection
