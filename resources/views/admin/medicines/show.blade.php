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
                <label class="text-sm text-gray-500">Satuan Penjualan</label>
                <p class="text-gray-800">{{ $medicine->unit }}</p>
            </div>
            @if($medicine->expired_date)
            <div>
                <label class="text-sm text-gray-500">Tanggal Kadaluarsa</label>
                <p class="text-gray-800">{{ $medicine->expired_date->format('d/m/Y') }}</p>
            </div>
            @endif
        </div>

        <!-- Edit Data Obat (1 tombol simpan) -->
        <div class="mt-6 border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Edit Data Obat</h3>
            @php
                $latestPenerimaan = $penerimaanDetails->first();
                // Jika ada history penerimaan, ambil dari penerimaan terakhir
                // Jika tidak ada, ambil dari kolom medicines (untuk obat hasil import Excel)
                $hargaBeli = $latestPenerimaan ? ($latestPenerimaan->price ?? 0) : ($medicine->purchase_price ?? 0);
                $hargaJual = $medicine->price ?? 0;
                $marginPercent = $latestPenerimaan ? ($latestPenerimaan->margin_percent ?? 0) : ($medicine->margin_percent ?? 0);
                $hasPenerimaan = $penerimaanDetails->count() > 0;
            @endphp

            @if(!$hasPenerimaan)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                    <span class="text-sm text-yellow-700">Obat ini belum memiliki history penerimaan. Anda bisa mengubah stok, harga beli, dan margin dari sini.</span>
                </div>
            </div>
            @else
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span class="text-sm text-blue-700">Obat ini memiliki history penerimaan. Stok tidak bisa diubah dari sini (gunakan menu Penerimaan Farmasi).</span>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-500 mb-2">Satuan Penjualan (Unit)</label>
                    <input type="text" id="unit" value="{{ $medicine->unit }}"
                        list="unitOptions"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="contoh: box / strip / tablet / botol">
                    <datalist id="unitOptions">
                        <option value="box"></option>
                        <option value="strip"></option>
                        <option value="tablet"></option>
                        <option value="kapsul"></option>
                        <option value="botol"></option>
                        <option value="sachet"></option>
                        <option value="ampul"></option>
                        <option value="vial"></option>
                        <option value="pcs"></option>
                        <option value="ml"></option>
                    </datalist>
                    <p class="text-xs text-gray-400 mt-1">Unit ini dipakai untuk tampilan stok & penjualan.</p>
                </div>

                <div>
                    <label class="block text-sm text-gray-500 mb-2">Jumlah Stok</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="stock" inputmode="numeric" value="{{ $medicine->stock }}"
                            {{ $hasPenerimaan ? 'disabled' : '' }}
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 disabled:bg-gray-100 disabled:text-gray-500"
                            placeholder="Masukkan jumlah stok (contoh: 1000)"
                            oninput="formatStockInput(this)">
                        <span class="text-sm text-gray-500">{{ $medicine->unit }}</span>
                    </div>
                    @if($hasPenerimaan)
                        <p class="text-xs text-gray-400 mt-1">Stok diubah lewat Penerimaan Farmasi.</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-sm text-gray-500 mb-2">Harga Beli</label>
                    <div class="flex items-center gap-2">
                        <input type="number" id="purchase_price" step="1" min="0" value="{{ round($hargaBeli) }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            placeholder="Harga beli">
                        <span class="text-sm text-gray-500">/ <span id="unit_suffix_a">{{ $medicine->unit }}</span></span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-500 mb-2">Harga Jual</label>
                    <div class="flex items-center gap-2">
                        <input type="number" id="selling_price" step="1" min="0" value="{{ round($hargaJual) }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            placeholder="Harga jual">
                        <span class="text-sm text-gray-500">/ <span id="unit_suffix_b">{{ $medicine->unit }}</span></span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Jika kosong, akan dihitung dari margin.</p>
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
                    </div>
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="button" onclick="saveMedicineInfo()"
                    class="px-6 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
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
// Format input stok: biarkan user ketik angka langsung tanpa ribet
function formatStockInput(input) {
    // Hapus semua karakter selain angka
    let value = input.value.replace(/[^\d]/g, '');
    // Update value tanpa format (biar simple)
    input.value = value;
}

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

// Update suffix unit di UI jika user mengubah satuan
document.addEventListener('DOMContentLoaded', function () {
    const unitInput = document.getElementById('unit');
    if (!unitInput) return;
    unitInput.addEventListener('input', function () {
        const val = unitInput.value || '';
        const a = document.getElementById('unit_suffix_a');
        const b = document.getElementById('unit_suffix_b');
        if (a) a.textContent = val;
        if (b) b.textContent = val;
    });
});

function saveMedicineInfo() {
    const medicineId = {{ $medicine->id }};
    const routePrefix = '{{ request()->routeIs("karyawan.*") ? "karyawan" : "admin" }}';

    const unit = (document.getElementById('unit')?.value || '').trim();
    const stockEl = document.getElementById('stock');
    const purchasePrice = parseFloat(document.getElementById('purchase_price')?.value || '0') || 0;
    const sellingPrice = parseFloat(document.getElementById('selling_price')?.value || '0') || 0;
    const marginPercent = parseFloat(document.getElementById('margin_percent')?.value || '0') || 0;

    if (!unit) {
        alert('Satuan penjualan (unit) wajib diisi!');
        return;
    }
    if (purchasePrice < 0 || sellingPrice < 0 || marginPercent < 0) {
        alert('Nilai tidak boleh negatif!');
        return;
    }

    const payload = {
        unit: unit,
        purchase_price: purchasePrice,
        margin_percent: marginPercent,
    };

    // Jika user mengisi harga jual, kirim; jika tidak, biarkan server hitung dari margin
    if (sellingPrice > 0) {
        payload.selling_price = sellingPrice;
    }

    // Jika stok enabled (hanya untuk obat tanpa penerimaan), ikut kirim
    if (stockEl && !stockEl.disabled) {
        // Hapus semua karakter non-digit, lalu parse
        const stockRaw = (stockEl.value || '0').replace(/[^\d]/g, '');
        const stockVal = parseInt(stockRaw, 10);
        if (isNaN(stockVal) || stockVal < 0) {
            alert('Stok tidak valid!');
            return;
        }
        payload.stock = stockVal;
    }

    fetch(`/${routePrefix}/medicines/${medicineId}/update-info`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert(result.message || 'Data berhasil diperbarui!');
            location.reload();
        } else {
            alert('Terjadi kesalahan: ' + (result.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan!');
    });
}
</script>
@endpush
@endsection







