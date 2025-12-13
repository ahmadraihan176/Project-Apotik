@extends('layouts.admin')

@section('title', 'Jatuh Tempo')
@section('header', 'Jatuh Tempo')

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-red-600 font-medium">Sudah Jatuh Tempo</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $sudahJatuhTempo->count() }}</p>
                </div>
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i>
            </div>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-orange-600 font-medium">Akan Jatuh Tempo (≤7 hari)</p>
                    <p class="text-2xl font-bold text-orange-700 mt-1">{{ $akanJatuhTempo->count() }}</p>
                </div>
                <i class="fas fa-clock text-orange-500 text-3xl"></i>
            </div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-600 font-medium">Belum Jatuh Tempo</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ $belumJatuhTempo->count() }}</p>
                </div>
                <i class="fas fa-calendar-check text-blue-500 text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Daftar Penerimaan Tempo -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Daftar Pembelian Tempo</h3>
        
        @if($penerimaanTempo->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Penerimaan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Penerimaan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grand Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dibuat Oleh</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($penerimaanTempo as $penerimaan)
                    @php
                        // Hitung selisih hari dengan membulatkan ke bilangan bulat
                        $daysUntilDue = (int) round(now()->startOfDay()->diffInDays($penerimaan->jatuh_tempo->startOfDay(), false));
                        $isOverdue = $daysUntilDue < 0;
                        $isDueSoon = $daysUntilDue >= 0 && $daysUntilDue <= 7;
                    @endphp
                    <tr class="{{ $isOverdue ? 'bg-red-50' : ($isDueSoon ? 'bg-orange-50' : '') }}">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $penerimaan->receipt_date->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $penerimaan->receipt_code }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $penerimaan->supplier_name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <div class="font-semibold {{ $isOverdue ? 'text-red-700' : ($isDueSoon ? 'text-orange-700' : 'text-blue-700') }}">
                                {{ $penerimaan->jatuh_tempo->format('d/m/Y') }}
                            </div>
                            @if($isOverdue)
                                <div class="text-xs text-red-600 font-semibold">
                                    Terlambat {{ abs($daysUntilDue) }} hari
                                </div>
                            @elseif($isDueSoon)
                                <div class="text-xs text-orange-600 font-semibold">
                                    {{ number_format($daysUntilDue, 0, ',', '.') }} hari lagi
                                </div>
                            @else
                                <div class="text-xs text-gray-500">
                                    {{ number_format($daysUntilDue, 0, ',', '.') }} hari lagi
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            @if($isOverdue)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Terlambat
                                </span>
                            @elseif($isDueSoon)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                    Akan Jatuh Tempo
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Belum Jatuh Tempo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-semibold">
                            Rp {{ number_format($penerimaan->grand_total, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $penerimaan->user->name }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            @php
                                $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
                            @endphp
                            <form action="{{ route($routePrefix . '.jatuh-tempo.mark-paid', $penerimaan->id) }}" method="POST" 
                                onsubmit="return confirm('Yakin ingin menandai pembelian ini sebagai sudah dibayar?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm">
                                    <i class="fas fa-check mr-2"></i>Sudah Dibayar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">Belum ada pembelian dengan tempo</p>
        @endif
    </div>
</div>
@endsection




