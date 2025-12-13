@extends('layouts.admin')

@section('title', 'Detail Obat')
@section('header', 'Detail Obat')

@section('content')
<div class="space-y-6">
    <!-- Informasi Obat -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $medicine->name }}</h2>
                <p class="text-gray-500">Kode: {{ $medicine->code }}</p>
            </div>
            <a href="{{ route('admin.medicines.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6">
            <div>
                <label class="text-sm text-gray-500">Deskripsi</label>
                <p class="text-gray-800">{{ $medicine->description ?? '-' }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Harga Jual</label>
                <p class="text-gray-800 font-semibold">Rp {{ number_format($medicine->price, 0, ',', '.') }} / {{ $medicine->unit }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Stok</label>
                <p class="text-gray-800">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $medicine->stock <= 10 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        {{ $medicine->stock }} {{ $medicine->unit }}
                    </span>
                </p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Unit</label>
                <p class="text-gray-800">{{ $medicine->unit }}</p>
            </div>
            @if($medicine->expired_date)
            <div>
                <label class="text-sm text-gray-500">Tanggal Kadaluarsa</label>
                <p class="text-gray-800">{{ $medicine->expired_date->format('d/m/Y') }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- History Penerimaan -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">History Penerimaan</h3>
        
        @if($penerimaanDetails->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Penerimaan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Pembayaran</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. SP / Faktur</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan Pembelian</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Jual</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Batch</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expired</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Beli (Unit Jual)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diskon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($penerimaanDetails as $detail)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->penerimaanBarang->receipt_date->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->penerimaanBarang->receipt_code }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->penerimaanBarang->supplier_name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $detail->penerimaanBarang->jenis_pembayaran === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $detail->penerimaanBarang->jenis_pembayaran === 'cash' ? 'Cash' : 'Tempo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            @if($detail->penerimaanBarang->jenis_pembayaran === 'tempo' && $detail->penerimaanBarang->jatuh_tempo)
                                <div class="font-semibold text-blue-700">
                                    {{ $detail->penerimaanBarang->jatuh_tempo->format('d/m/Y') }}
                                </div>
                                @php
                                    // Hitung selisih hari dengan membulatkan ke bilangan bulat
                                    $daysUntilDue = (int) round(now()->startOfDay()->diffInDays($detail->penerimaanBarang->jatuh_tempo->startOfDay(), false));
                                @endphp
                                @if($daysUntilDue < 0)
                                    <div class="text-xs text-red-600 font-semibold">
                                        Terlambat {{ abs($daysUntilDue) }} hari
                                    </div>
                                @elseif($daysUntilDue <= 7)
                                    <div class="text-xs text-orange-600 font-semibold">
                                        {{ number_format($daysUntilDue, 0, ',', '.') }} hari lagi
                                    </div>
                                @else
                                    <div class="text-xs text-gray-500">
                                        {{ number_format($daysUntilDue, 0, ',', '.') }} hari lagi
                                    </div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            @if($detail->penerimaanBarang->no_sp || $detail->penerimaanBarang->no_faktur)
                                @if($detail->penerimaanBarang->no_sp)
                                    <div class="text-xs">SP: {{ $detail->penerimaanBarang->no_sp }}</div>
                                @endif
                                @if($detail->penerimaanBarang->no_faktur)
                                    <div class="text-xs">Faktur: {{ $detail->penerimaanBarang->no_faktur }}</div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->unit_kemasan ?? '-' }}
                            @if($detail->unit_kemasan === 'box' && $detail->isi_per_box)
                                <div class="text-xs text-gray-500">({{ $detail->isi_per_box }} {{ $detail->unit_jual }}/box)</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->unit_jual ?? $medicine->unit ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->quantity }} {{ $detail->unit_kemasan ?? $detail->unit_jual ?? '-' }}
                            @if($detail->unit_kemasan === 'box' && $detail->isi_per_box)
                                <div class="text-xs text-gray-500">= {{ $detail->quantity * $detail->isi_per_box }} {{ $detail->unit_jual }}</div>
                            @elseif($detail->unit_kemasan && $detail->unit_kemasan !== $detail->unit_jual)
                                <div class="text-xs text-gray-500">({{ $detail->unit_jual }})</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->no_batch ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->expired_date ? $detail->expired_date->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($detail->price, 0, ',', '.') }} / {{ $detail->unit_jual ?? $medicine->unit }}
                            @if($detail->unit_kemasan && $detail->unit_kemasan !== $detail->unit_jual)
                                <div class="text-xs text-gray-500">(Beli: {{ $detail->unit_kemasan }})</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            @if($detail->discount_amount > 0)
                                {{ $detail->discount_percent > 0 ? $detail->discount_percent . '%' : '' }}
                                <br>
                                <span class="text-xs text-gray-500">Rp {{ number_format($detail->discount_amount, 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-semibold">
                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">Belum ada history penerimaan</p>
        @endif
    </div>

    <!-- History Penjualan -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">History Penjualan</h3>
        
        @if($penjualanDetails->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Transaksi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($penjualanDetails as $detail)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->transaction->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->transaction->transaction_code }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->quantity }} {{ $medicine->unit }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($detail->price, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-semibold">
                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">Belum ada history penjualan</p>
        @endif
    </div>
</div>
@endsection




