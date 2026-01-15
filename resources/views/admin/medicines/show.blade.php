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
            <div class="flex gap-2">
                @php
                    $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
                @endphp
                <a href="{{ route($routePrefix . '.medicines.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6">
            <div>
                <label class="text-sm text-gray-500">Deskripsi</label>
                <p class="text-gray-800">{{ $medicine->description ?? '-' }}</p>
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
        
        <!-- Harga Beli, Harga Jual, dan Margin -->
        <div class="mt-6 border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Harga</h3>
            @php
                // Ambil data penerimaan terakhir untuk mendapatkan harga beli dan margin
                $latestPenerimaan = $penerimaanDetails->first();
                $hargaBeli = $latestPenerimaan ? $latestPenerimaan->price : 0;
                $hargaJual = $medicine->price;
                $marginPercent = $latestPenerimaan ? $latestPenerimaan->margin_percent : 0;
            @endphp
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-500 mb-2">Harga Beli</label>
                    <div class="flex items-center gap-2">
                        <input type="number" id="purchase_price" step="1" min="0" value="{{ round($hargaBeli) }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            placeholder="Harga beli">
                        <span class="text-sm text-gray-500">/ {{ $medicine->unit }}</span>
                        <button type="button" onclick="updateMedicinePrice('purchase')" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            <i class="fas fa-save"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-500 mb-2">Harga Jual</label>
                    <div class="flex items-center gap-2">
                        <input type="number" id="selling_price" step="1" min="0" value="{{ round($hargaJual) }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            placeholder="Harga jual">
                        <span class="text-sm text-gray-500">/ {{ $medicine->unit }}</span>
                        <button type="button" onclick="updateMedicinePrice('selling')" 
                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                            <i class="fas fa-save"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-500 mb-2">Margin (%)</label>
                    <div class="flex items-center gap-2">
                        <input type="number" id="margin_percent" step="1" min="0" value="{{ round($marginPercent) }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            placeholder="Margin %"
                            onchange="calculateSellingPriceFromMargin()"
                            onkeyup="calculateSellingPriceFromMargin()">
                        <span class="text-sm text-gray-500">%</span>
                        <button type="button" onclick="updateMedicinePrice('margin')" 
                            class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                            <i class="fas fa-save"></i>
                        </button>
                    </div>
                </div>
            </div>
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Jual</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Margin %</th>
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
                            Rp {{ number_format(round($detail->selling_price ?? 0), 0, ',', '.') }} / {{ $detail->unit_jual ?? $medicine->unit }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($detail->margin_percent ?? 0, 2, ',', '.') }}%
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

@push('scripts')
<script>
function calculateSellingPriceFromMargin() {
    const purchasePriceInput = document.getElementById('purchase_price');
    const marginPercentInput = document.getElementById('margin_percent');
    const sellingPriceInput = document.getElementById('selling_price');
    
    if (purchasePriceInput && marginPercentInput && sellingPriceInput) {
        const purchasePrice = parseFloat(purchasePriceInput.value) || 0;
        const marginPercent = parseFloat(marginPercentInput.value) || 0;
        
        if (purchasePrice > 0) {
            // Harga beli sudah termasuk PPN, jadi langsung hitung dengan margin
            // Jika margin 0%, harga jual = harga beli
            // Jika margin > 0%, harga jual = harga beli * (1 + margin/100)
            const sellingPrice = purchasePrice * (1 + marginPercent / 100);
            sellingPriceInput.value = Math.round(sellingPrice);
        }
    }
}

function updateMedicinePrice(type) {
    const medicineId = {{ $medicine->id }};
    let data = {};
    
    if (type === 'purchase') {
        const purchasePrice = parseFloat(document.getElementById('purchase_price').value) || 0;
        if (purchasePrice <= 0) {
            alert('Harga beli harus lebih dari 0!');
            return;
        }
        data = { purchase_price: purchasePrice };
    } else if (type === 'selling') {
        const sellingPrice = parseFloat(document.getElementById('selling_price').value) || 0;
        if (sellingPrice <= 0) {
            alert('Harga jual harus lebih dari 0!');
            return;
        }
        data = { selling_price: sellingPrice };
    } else if (type === 'margin') {
        const marginPercent = parseFloat(document.getElementById('margin_percent').value) || 0;
        if (marginPercent < 0) {
            alert('Margin tidak boleh negatif!');
            return;
        }
        
        // Hitung ulang harga jual berdasarkan margin baru
        const purchasePriceInput = document.getElementById('purchase_price');
        const sellingPriceInput = document.getElementById('selling_price');
        
        if (purchasePriceInput && sellingPriceInput) {
            const purchasePrice = parseFloat(purchasePriceInput.value) || 0;
            
            if (purchasePrice > 0) {
                // Harga beli sudah termasuk PPN, jadi langsung hitung dengan margin
                // Jika margin 0%, harga jual = harga beli
                // Jika margin > 0%, harga jual = harga beli * (1 + margin/100)
                const sellingPrice = purchasePrice * (1 + marginPercent / 100);
                sellingPriceInput.value = Math.round(sellingPrice);
                
                // Kirim margin dan harga jual yang baru
                data = { 
                    margin_percent: marginPercent,
                    selling_price: Math.round(sellingPrice)
                };
            } else {
                data = { margin_percent: marginPercent };
            }
        } else {
            data = { margin_percent: marginPercent };
        }
    }
    
    // Kirim request ke server
    fetch(`/admin/medicines/${medicineId}/update-price`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Data berhasil diperbarui!');
            location.reload();
        } else {
            alert('Terjadi kesalahan: ' + (result.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memperbarui data!');
    });
}
</script>
@endpush
@endsection







