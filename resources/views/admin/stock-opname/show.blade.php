@extends('layouts.admin')

@section('title', 'Detail Stok Opname')
@section('header', 'Detail Stok Opname')

@section('content')
<div class="space-y-6">
    <!-- Header Info -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-sm text-gray-500">Tanggal Opname</label>
                <p class="text-lg font-semibold text-gray-800">{{ $stockOpname->opname_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Dibuat Oleh</label>
                <p class="text-lg font-semibold text-gray-800">{{ $stockOpname->user->name }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Status</label>
                <p>
                    @if($stockOpname->status === 'draft')
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                    @elseif($stockOpname->status === 'completed')
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Selesai</span>
                    @else
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                    @endif
                </p>
            </div>
            @if($stockOpname->approver)
            <div>
                <label class="text-sm text-gray-500">Disetujui Oleh</label>
                <p class="text-lg font-semibold text-gray-800">{{ $stockOpname->approver->name }}</p>
                <p class="text-sm text-gray-500">{{ $stockOpname->approved_at->format('d/m/Y H:i') }}</p>
            </div>
            @endif
        </div>
        @if($stockOpname->notes)
        <div>
            <label class="text-sm text-gray-500">Catatan</label>
            <p class="text-gray-800">{{ $stockOpname->notes }}</p>
        </div>
        @endif
    </div>

    <!-- Detail Items -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Detail Obat</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Obat</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kadaluarsa</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kondisi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok Sistem</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok Fisik</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selisih</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($stockOpname->details as $detail)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->medicine->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->batch_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $detail->expired_date ? $detail->expired_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $detail->condition === 'baik' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $detail->condition === 'rusak' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $detail->condition === 'kadaluarsa' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $detail->condition === 'hampir_kadaluarsa' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $detail->condition === 'retur' ? 'bg-orange-100 text-orange-800' : '' }}">
                                    {{ $detail->condition_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->system_stock }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->physical_stock }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $detail->difference > 0 ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $detail->difference < 0 ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $detail->difference == 0 ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ $detail->difference > 0 ? '+' : '' }}{{ $detail->difference }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex space-x-4">
        <a href="{{ route('admin.stock-opname.edit', $stockOpname) }}" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            <i class="fas fa-edit mr-2"></i>Edit
        </a>
        @if(!$stockOpname->isApproved())
            @if($stockOpname->isCompleted())
                <form action="{{ route('admin.stock-opname.approve', $stockOpname) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menyetujui opname ini? Stok akan disesuaikan otomatis.')">
                    @csrf
                    <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                        <i class="fas fa-check mr-2"></i>Setujui & Sesuaikan Stok
                    </button>
                </form>
            @endif
        @else
            <div class="px-6 py-2 bg-yellow-100 text-yellow-800 rounded-lg border border-yellow-300">
                <i class="fas fa-info-circle mr-2"></i>
                <span class="text-sm">Update akan reset status approval dan revert stok</span>
            </div>
        @endif
        <a href="{{ route('admin.stock-opname.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
</div>
@endsection

