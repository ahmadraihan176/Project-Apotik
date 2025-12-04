@extends('layouts.admin')

@section('title', 'Penerimaan Farmasi')
@section('header', 'Penerimaan Farmasi')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <form action="{{ route('admin.penerimaan-farmasi.store') }}" method="POST" id="receiptForm" novalidate>
        @csrf
        
        <!-- Informasi Umum Penerimaan -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Penerimaan</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Tanggal *</label>
                    <input type="date" name="receipt_date" value="{{ old('receipt_date', date('Y-m-d')) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 @error('receipt_date') border-red-500 @enderror">
                    @error('receipt_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Supplier</label>
                    <input type="text" name="supplier_name" id="supplier_name" value="{{ old('supplier_name') }}"
                        list="supplier_list"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Nama supplier (ketik atau pilih dari daftar)">
                    <datalist id="supplier_list">
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier }}">
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Jenis Penerimaan</label>
                    <select name="jenis_penerimaan" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Jenis Penerimaan</option>
                        <option value="Pembelian" {{ old('jenis_penerimaan') == 'Pembelian' ? 'selected' : '' }}>Pembelian</option>
                        <option value="Retur" {{ old('jenis_penerimaan') == 'Retur' ? 'selected' : '' }}>Retur</option>
                        <option value="Transfer" {{ old('jenis_penerimaan') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="Konsinyasi" {{ old('jenis_penerimaan') == 'Konsinyasi' ? 'selected' : '' }}>Konsinyasi</option>
                        <option value="Hibah" {{ old('jenis_penerimaan') == 'Hibah' ? 'selected' : '' }}>Hibah</option>
                        <option value="Lainnya" {{ old('jenis_penerimaan') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">No. SP</label>
                    <input type="text" name="no_sp" value="{{ old('no_sp') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Nomor SP">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">No. Faktur</label>
                    <input type="text" name="no_faktur" value="{{ old('no_faktur') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Nomor faktur">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Jenis Pembayaran *</label>
                    <select name="jenis_pembayaran" required id="jenis_pembayaran" onchange="toggleJatuhTempo()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="cash" {{ old('jenis_pembayaran', 'cash') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="tempo" {{ old('jenis_pembayaran') == 'tempo' ? 'selected' : '' }}>Tempo</option>
                    </select>
                </div>
                <div id="jatuh_tempo_field">
                    <label class="block text-gray-700 font-semibold mb-2">Jatuh Tempo</label>
                    <input type="date" name="jatuh_tempo" value="{{ old('jatuh_tempo') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Diterima Semua</label>
                    <select name="diterima_semua" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Status</option>
                        <option value="Ya" {{ old('diterima_semua') == 'Ya' ? 'selected' : '' }}>Ya</option>
                        <option value="Tidak" {{ old('diterima_semua') == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                        <option value="Sebagian" {{ old('diterima_semua') == 'Sebagian' ? 'selected' : '' }}>Sebagian</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">No. Urut</label>
                    <input type="text" name="no_urut" value="{{ old('no_urut') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Nomor urut">
                </div>
            </div>
        </div>

        <!-- Form Tambah Item -->
        <div class="mb-6 border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Barang</h3>
            <div class="grid grid-cols-5 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Kode Produk</label>
                    <input type="text" id="product_code_input" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Kode produk">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Nama Barang *</label>
                    <input type="text" id="medicine_name_input" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Nama obat">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Harga (Rp) *</label>
                    <input type="number" id="price_input" step="0.01" min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Harga per satuan pembelian"
                        title="Harga per satuan pembelian (misal: per box, per strip)">
                    <p class="text-xs text-gray-500 mt-1">Harga per satuan pembelian</p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Unit Jual *</label>
                    <select id="unit_jual_select"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        title="Satuan untuk penjualan ke customer">
                        <option value="">Pilih...</option>
                        <option value="strip">Strip</option>
                        <option value="tablet">Tablet</option>
                        <option value="ml">ML</option>
                        <option value="gram">Gram</option>
                        <option value="botol">Botol</option>
                        <option value="tube">Tube</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Satuan untuk penjualan</p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Jumlah *</label>
                    <input type="number" id="quantity_input" min="1" value="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Jumlah...">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Satuan Pembelian</label>
                    <select id="unit_kemasan_select" onchange="toggleIsiPerBox()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        title="Satuan saat membeli dari supplier (misal: box, strip, botol)">
                        <option value="">Pilih...</option>
                        <option value="box">Box</option>
                        <option value="strip">Strip</option>
                        <option value="tablet">Tablet</option>
                        <option value="botol">Botol</option>
                        <option value="tube">Tube</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Satuan saat membeli</p>
                </div>
                <div id="isi_per_box_field" style="display: none;">
                    <label class="block text-gray-700 font-semibold mb-2">Isi per Box *</label>
                    <input type="number" id="isi_per_box_input" min="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Jumlah strip/tablet per box">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Deskripsi</label>
                    <input type="text" id="description_input"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Deskripsi (opsional)">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">No. Batch</label>
                    <input type="text" id="no_batch_input"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Nomor batch">
                    <div class="flex gap-4 mt-4">
                        <button type="button" onclick="addItem()" class="flex-1 px-4 py-2 gradient-bg text-white rounded-lg hover:opacity-90">
                            <i class="fas fa-plus mr-2"></i>Tambahkan
                        </button>
                        <button type="button" onclick="addItemAndContinue()" class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                            <i class="fas fa-plus-circle mr-2"></i>Tambahkan & Lanjutkan
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Expired Date</label>
                    <input type="date" id="expired_date_input"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Diskon (%)</label>
                    <input type="number" id="discount_percent_input" step="0.01" min="0" max="100" value="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="0"
                        onchange="calculateItemDiscount()">
                </div>
            </div>
            <p class="text-xs text-gray-500 italic">
                *Jika harga barang desimal / Terdapat koma (Cth: Rp. 1000,34) Maka wajib input menggunakan tanda Titik (.) dan secara otomatis berubah mengikuti format
            </p>
        </div>

        <!-- Tabel Item -->
        <div class="mb-6 border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Daftar Barang</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="itemsTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NO.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">KODE PRODUK</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NAMA BARANG</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SATUAN PEMBELIAN</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">UNIT JUAL</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NO BATCH</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">EXPIRED</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">JUMLAH</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">HARGA @ (Pembelian)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DISC %</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DISC RP.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SUBTOTAL</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Items will be added here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Total -->
        <div class="grid grid-cols-2 gap-6 mb-6 border-t pt-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Catatan</h3>
                <textarea name="notes" rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                    placeholder="Catatan tambahan">{{ old('notes') }}</textarea>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">TOTAL</h3>
                <div class="space-y-3">
                    <div class="flex gap-2">
                        <input type="number" name="discount_percent" id="discount_percent" step="0.01" min="0" max="100" value="{{ old('discount_percent', 0) }}"
                            class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            onchange="calculateTotals()" placeholder="%">
                        <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0" value="{{ old('discount_amount', 0) }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            onchange="calculateTotals()" placeholder="Rp">
                        <label class="flex items-center text-gray-700 font-semibold">Diskon</label>
                    </div>
                    <div class="flex gap-2">
                        <input type="number" name="ppn_percent" id="ppn_percent" step="0.01" min="0" max="100" value="{{ old('ppn_percent', 11) }}"
                            class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            onchange="calculateTotals()" placeholder="%" readonly>
                        <input type="number" name="ppn_amount" id="ppn_amount" step="0.01" min="0" value="{{ old('ppn_amount', 0) }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-sky-500"
                            placeholder="Rp" readonly>
                        <label class="flex items-center text-gray-700 font-semibold">PPN (11%)</label>
                    </div>
                    <div class="flex gap-2">
                        <input type="number" name="materai" id="materai" step="0.01" min="0" value="{{ old('materai', 0) }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            onchange="calculateTotals()" placeholder="Rp">
                        <label class="flex items-center text-gray-700 font-semibold">Materai</label>
                    </div>
                    <div class="flex gap-2">
                        <input type="number" id="total_before_discount" step="0.01" readonly
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" value="0">
                        <label class="flex items-center text-gray-700 font-semibold">Total</label>
                    </div>
                    <div class="flex gap-2">
                        <input type="number" name="extra_discount_percent" id="extra_discount_percent" step="0.01" min="0" max="100" value="{{ old('extra_discount_percent', 0) }}"
                            class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            onchange="calculateTotals()" placeholder="%">
                        <input type="number" name="extra_discount_amount" id="extra_discount_amount" step="0.01" min="0" value="{{ old('extra_discount_amount', 0) }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            onchange="calculateTotals()" placeholder="Rp">
                        <label class="flex items-center text-gray-700 font-semibold">Extra Diskon</label>
                    </div>
                    <div class="flex gap-2">
                        <input type="number" id="grand_total" step="0.01" readonly
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 font-bold text-lg" value="0">
                        <label class="flex items-center text-gray-700 font-semibold">Grand Total</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex space-x-4">
            <button type="submit" class="flex-1 gradient-bg text-white font-semibold py-2 rounded-lg hover:opacity-90">
                <i class="fas fa-save mr-2"></i>Simpan
            </button>
            <a href="{{ route('admin.medicines.index') }}" class="flex-1 text-center bg-gray-300 text-gray-700 font-semibold py-2 rounded-lg hover:bg-gray-400">
                <i class="fas fa-times mr-2"></i>Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let itemCount = 0;
    let items = [];

    function toggleJatuhTempo() {
        const jenisPembayaran = document.getElementById('jenis_pembayaran').value;
        const jatuhTempoField = document.getElementById('jatuh_tempo_field');
        const jatuhTempoInput = jatuhTempoField.querySelector('input');
        
        if (jenisPembayaran === 'tempo') {
            jatuhTempoInput.required = true;
            jatuhTempoField.style.display = 'block';
        } else {
            jatuhTempoInput.required = false;
            jatuhTempoField.style.display = 'none';
        }
    }

    function toggleIsiPerBox() {
        const unitKemasan = document.getElementById('unit_kemasan_select').value;
        const isiPerBoxField = document.getElementById('isi_per_box_field');
        const isiPerBoxInput = document.getElementById('isi_per_box_input');
        
        if (unitKemasan === 'box') {
            isiPerBoxInput.required = true;
            isiPerBoxField.style.display = 'block';
        } else {
            isiPerBoxInput.required = false;
            isiPerBoxField.style.display = 'none';
            isiPerBoxInput.value = '';
        }
    }

    function addItem() {
        const medicineNameInput = document.getElementById('medicine_name_input');
        const priceInput = document.getElementById('price_input');
        const unitJualSelect = document.getElementById('unit_jual_select');
        const unitKemasanSelect = document.getElementById('unit_kemasan_select');
        const isiPerBoxInput = document.getElementById('isi_per_box_input');
        const quantityInput = document.getElementById('quantity_input');
        const descriptionInput = document.getElementById('description_input');
        const noBatchInput = document.getElementById('no_batch_input');
        const expiredDateInput = document.getElementById('expired_date_input');
        const discountPercentInput = document.getElementById('discount_percent_input');
        const productCodeInput = document.getElementById('product_code_input');
        
        const medicineName = medicineNameInput.value.trim();
        const price = parseFloat(priceInput.value) || 0;
        const unitJual = unitJualSelect.value;
        const unitKemasan = unitKemasanSelect.value || unitJual;
        const quantity = parseInt(quantityInput.value) || 1;
        const isiPerBox = unitKemasan === 'box' ? parseInt(isiPerBoxInput.value) || 0 : null;
        const description = descriptionInput.value.trim();
        const noBatch = noBatchInput.value.trim();
        const expiredDate = expiredDateInput.value || null;
        const productCode = productCodeInput.value.trim();
        
        // Hitung diskon dari persen
        const discountPercent = parseFloat(discountPercentInput.value) || 0;
        const itemSubtotal = price * quantity;
        const discountAmount = itemSubtotal * discountPercent / 100;

        // Validasi
        if (!medicineName) {
            alert('Nama barang harus diisi!');
            return;
        }
        if (!price || price <= 0) {
            alert('Harga harus diisi dan lebih dari 0!');
            return;
        }
        if (!unitJual) {
            alert('Unit jual harus dipilih!');
            return;
        }
        if (!quantity || quantity <= 0) {
            alert('Jumlah harus diisi dan lebih dari 0!');
            return;
        }
        if (unitKemasan === 'box' && (!isiPerBox || isiPerBox <= 0)) {
            alert('Isi per box harus diisi jika kemasan adalah box!');
            return;
        }

        itemCount++;
        const item = {
            id: itemCount,
            product_code: productCode,
            medicine_name: medicineName,
            unit_jual: unitJual,
            unit_kemasan: unitKemasan,
            isi_per_box: isiPerBox,
            quantity: quantity,
            price: price,
            description: description,
            discount_percent: discountPercent,
            discount_amount: discountAmount,
            no_batch: noBatch,
            expired_date: expiredDate
        };

        items.push(item);
        renderItemsTable();
        
        // Reset form
        productCodeInput.value = '';
        medicineNameInput.value = '';
        priceInput.value = '';
        unitJualSelect.value = '';
        unitKemasanSelect.value = '';
        isiPerBoxInput.value = '';
        quantityInput.value = 1;
        descriptionInput.value = '';
        noBatchInput.value = '';
        expiredDateInput.value = '';
        discountPercentInput.value = 0;
        toggleIsiPerBox();
        
        // Focus ke nama barang untuk input berikutnya
        medicineNameInput.focus();
    }

    function addItemAndContinue() {
        const medicineNameInput = document.getElementById('medicine_name_input');
        const priceInput = document.getElementById('price_input');
        const unitJualSelect = document.getElementById('unit_jual_select');
        const unitKemasanSelect = document.getElementById('unit_kemasan_select');
        const isiPerBoxInput = document.getElementById('isi_per_box_input');
        const quantityInput = document.getElementById('quantity_input');
        const descriptionInput = document.getElementById('description_input');
        const noBatchInput = document.getElementById('no_batch_input');
        const expiredDateInput = document.getElementById('expired_date_input');
        const discountPercentInput = document.getElementById('discount_percent_input');
        const productCodeInput = document.getElementById('product_code_input');
        
        const medicineName = medicineNameInput.value.trim();
        const price = parseFloat(priceInput.value) || 0;
        const unitJual = unitJualSelect.value;
        const unitKemasan = unitKemasanSelect.value || unitJual;
        const quantity = parseInt(quantityInput.value) || 1;
        const isiPerBox = unitKemasan === 'box' ? parseInt(isiPerBoxInput.value) || 0 : null;
        const description = descriptionInput.value.trim();
        const noBatch = noBatchInput.value.trim();
        const expiredDate = expiredDateInput.value || null;
        const productCode = productCodeInput.value.trim();
        
        // Hitung diskon dari persen
        const discountPercent = parseFloat(discountPercentInput.value) || 0;
        const itemSubtotal = price * quantity;
        const discountAmount = itemSubtotal * discountPercent / 100;

        // Validasi
        if (!medicineName) {
            alert('Nama barang harus diisi!');
            return;
        }
        if (!price || price <= 0) {
            alert('Harga harus diisi dan lebih dari 0!');
            return;
        }
        if (!unitJual) {
            alert('Unit jual harus dipilih!');
            return;
        }
        if (!quantity || quantity <= 0) {
            alert('Jumlah harus diisi dan lebih dari 0!');
            return;
        }
        if (unitKemasan === 'box' && (!isiPerBox || isiPerBox <= 0)) {
            alert('Isi per box harus diisi jika kemasan adalah box!');
            return;
        }

        itemCount++;
        const item = {
            id: itemCount,
            product_code: productCode,
            medicine_name: medicineName,
            unit_jual: unitJual,
            unit_kemasan: unitKemasan,
            isi_per_box: isiPerBox,
            quantity: quantity,
            price: price,
            description: description,
            discount_percent: discountPercent,
            discount_amount: discountAmount,
            no_batch: noBatch,
            expired_date: expiredDate
        };

        items.push(item);
        renderItemsTable();
        
        // Reset hanya field yang berubah (kode, nama, harga, jumlah, deskripsi, batch, expired, diskon)
        // Pertahankan unit jual, satuan pembelian, dan isi per box
        productCodeInput.value = '';
        medicineNameInput.value = '';
        priceInput.value = '';
        quantityInput.value = 1;
        descriptionInput.value = '';
        noBatchInput.value = '';
        expiredDateInput.value = '';
        discountPercentInput.value = 0;
        
        // Focus ke nama barang untuk input berikutnya
        medicineNameInput.focus();
    }

    function calculateItemDiscount() {
        // Fungsi ini tetap ada untuk kompatibilitas, tapi tidak perlu melakukan apa-apa
        // karena diskon hanya dihitung dari persen saat addItem
    }

    function removeItem(itemId) {
        items = items.filter(item => item.id !== itemId);
        renderItemsTable();
        calculateTotals();
    }

    function duplicateItem(itemId) {
        const item = items.find(i => i.id === itemId);
        if (item) {
            itemCount++;
            const newItem = {
                id: itemCount,
                product_code: item.product_code,
                medicine_name: item.medicine_name,
                unit_jual: item.unit_jual,
                unit_kemasan: item.unit_kemasan,
                isi_per_box: item.isi_per_box,
                quantity: item.quantity,
                price: item.price,
                description: item.description,
                discount_percent: item.discount_percent,
                discount_amount: item.discount_amount,
                no_batch: item.no_batch,
                expired_date: item.expired_date
            };
            items.push(newItem);
            renderItemsTable();
        }
    }

    function renderItemsTable() {
        const tbody = document.getElementById('itemsTableBody');
        tbody.innerHTML = '';

        items.forEach((item, index) => {
            const subtotal = (item.price * item.quantity) - item.discount_amount;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-3 whitespace-nowrap text-sm">${index + 1}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">${item.product_code || '-'}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">${item.medicine_name}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <input type="text" name="details[${item.id}][unit_kemasan]" value="${item.unit_kemasan || ''}"
                        class="w-full px-2 py-1 border border-gray-300 rounded" onchange="updateItem(${item.id}, 'unit_kemasan', this.value)"
                        placeholder="Box/Strip/dll">
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <span class="text-gray-700 font-medium">${item.unit_jual || '-'}</span>
                    ${item.unit_kemasan === 'box' && item.isi_per_box ? `<span class="text-xs text-gray-500">(${item.isi_per_box}/box)</span>` : ''}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <input type="text" name="details[${item.id}][no_batch]" value="${item.no_batch || ''}"
                        class="w-full px-2 py-1 border border-gray-300 rounded" onchange="updateItem(${item.id}, 'no_batch', this.value)">
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <input type="date" name="details[${item.id}][expired_date]" 
                        value="${item.expired_date || ''}"
                        class="w-full px-2 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500" 
                        onchange="updateItem(${item.id}, 'expired_date', this.value)">
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <input type="number" name="details[${item.id}][quantity]" value="${item.quantity}" min="1"
                        class="w-20 px-2 py-1 border border-gray-300 rounded" onchange="updateItemQuantity(${item.id}, this.value)">
                    <span class="text-xs text-gray-500">${item.unit_kemasan || item.unit_jual || ''}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <input type="number" name="details[${item.id}][price]" value="${item.price}" step="0.01" min="0"
                        class="w-32 px-2 py-1 border border-gray-300 rounded" onchange="updateItemPrice(${item.id}, this.value)">
                    <span class="text-xs text-gray-500">/${item.unit_kemasan || item.unit_jual || ''}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <input type="number" name="details[${item.id}][discount_percent]" value="${item.discount_percent}" step="0.01" min="0" max="100"
                        class="w-20 px-2 py-1 border border-gray-300 rounded" onchange="updateItemDiscount(${item.id}, 'percent', this.value)">
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <input type="number" name="details[${item.id}][discount_amount]" value="${item.discount_amount}" step="0.01" min="0"
                        class="w-24 px-2 py-1 border border-gray-300 rounded" onchange="updateItemDiscount(${item.id}, 'amount', this.value)">
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold">${formatCurrency(subtotal)}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <div class="flex gap-2">
                        <button type="button" onclick="duplicateItem(${item.id})" class="text-blue-600 hover:text-blue-800" title="Duplikat item">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button type="button" onclick="removeItem(${item.id})" class="text-red-600 hover:text-red-800" title="Hapus item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
                <input type="hidden" name="details[${item.id}][medicine_name]" value="${item.medicine_name}">
                <input type="hidden" name="details[${item.id}][product_code]" value="${item.product_code || ''}">
                <input type="hidden" name="details[${item.id}][unit_jual]" value="${item.unit_jual}">
                <input type="hidden" name="details[${item.id}][isi_per_box]" value="${item.isi_per_box || ''}">
                <input type="hidden" name="details[${item.id}][description]" value="${item.description || ''}">
            `;
            tbody.appendChild(row);
        });

        calculateTotals();
    }

    function updateItem(itemId, field, value) {
        const item = items.find(i => i.id === itemId);
        if (item) {
            item[field] = value;
            renderItemsTable();
        }
    }

    function updateItemQuantity(itemId, quantity) {
        const item = items.find(i => i.id === itemId);
        if (item) {
            item.quantity = parseInt(quantity) || 1;
            renderItemsTable();
        }
    }

    function updateItemPrice(itemId, price) {
        const item = items.find(i => i.id === itemId);
        if (item) {
            item.price = parseFloat(price) || 0;
            renderItemsTable();
        }
    }

    function updateItemDiscount(itemId, type, value) {
        const item = items.find(i => i.id === itemId);
        if (item) {
            if (type === 'percent') {
                item.discount_percent = parseFloat(value) || 0;
                item.discount_amount = (item.price * item.quantity * item.discount_percent) / 100;
            } else {
                item.discount_amount = parseFloat(value) || 0;
                const itemTotal = item.price * item.quantity;
                item.discount_percent = itemTotal > 0 ? (item.discount_amount / itemTotal) * 100 : 0;
            }
            renderItemsTable();
        }
    }

    function calculateTotals() {
        // Hitung total dari items
        let subtotal = 0;
        items.forEach(item => {
            const itemSubtotal = (item.price * item.quantity) - item.discount_amount;
            subtotal += itemSubtotal;
        });

        document.getElementById('total_before_discount').value = subtotal.toFixed(2);

        // Hitung diskon global
        const discountPercent = parseFloat(document.getElementById('discount_percent').value) || 0;
        const discountAmountInput = document.getElementById('discount_amount');
        const discountAmountValue = parseFloat(discountAmountInput.value);
        
        // Jika discount_amount diisi manual, pakai itu. Jika tidak, hitung dari percent
        let finalDiscount = 0;
        if (discountAmountValue > 0 && discountAmountInput.value.trim() !== '') {
            finalDiscount = discountAmountValue;
        } else if (discountPercent > 0) {
            finalDiscount = subtotal * discountPercent / 100;
        }
        discountAmountInput.value = finalDiscount.toFixed(2);
        
        let totalAfterDiscount = subtotal - finalDiscount;

        // Hitung PPN (otomatis 11%)
        const ppnPercent = 11; // PPN selalu 11%
        const ppnAmountInput = document.getElementById('ppn_amount');
        const finalPPN = totalAfterDiscount * ppnPercent / 100;
        ppnAmountInput.value = finalPPN.toFixed(2);
        
        let totalAfterPPN = totalAfterDiscount + finalPPN;

        // Hitung extra diskon
        const extraDiscountPercent = parseFloat(document.getElementById('extra_discount_percent').value) || 0;
        const extraDiscountAmountInput = document.getElementById('extra_discount_amount');
        const extraDiscountAmountValue = parseFloat(extraDiscountAmountInput.value);
        
        let finalExtraDiscount = 0;
        if (extraDiscountAmountValue > 0 && extraDiscountAmountInput.value.trim() !== '') {
            finalExtraDiscount = extraDiscountAmountValue;
        } else if (extraDiscountPercent > 0) {
            finalExtraDiscount = totalAfterPPN * extraDiscountPercent / 100;
        }
        extraDiscountAmountInput.value = finalExtraDiscount.toFixed(2);
        
        // Hitung materai
        const materai = parseFloat(document.getElementById('materai').value) || 0;
        
        // Grand total
        const grandTotal = totalAfterPPN - finalExtraDiscount + materai;
        document.getElementById('grand_total').value = grandTotal.toFixed(2);
    }

    function formatCurrency(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        toggleJatuhTempo();
        toggleIsiPerBox();
        // Set PPN percent ke 11% dan hitung otomatis
        document.getElementById('ppn_percent').value = 11;
        calculateTotals();
    });

    // Validate form before submit
    document.getElementById('receiptForm').addEventListener('submit', function(e) {
        if (items.length === 0) {
            e.preventDefault();
            alert('Minimal harus ada 1 item barang!');
            return false;
        }
        
        // Validasi semua item punya unit_jual
        for (let i = 0; i < items.length; i++) {
            if (!items[i].unit_jual || items[i].unit_jual.trim() === '') {
                e.preventDefault();
                alert(`Item ke-${i + 1} (${items[i].medicine_name}) belum memiliki unit jual!`);
                return false;
            }
            if (!items[i].medicine_name || items[i].medicine_name.trim() === '') {
                e.preventDefault();
                alert(`Item ke-${i + 1} belum memiliki nama barang!`);
                return false;
            }
            if (!items[i].price || items[i].price <= 0) {
                e.preventDefault();
                alert(`Item ke-${i + 1} (${items[i].medicine_name}) harga harus lebih dari 0!`);
                return false;
            }
            if (!items[i].quantity || items[i].quantity <= 0) {
                e.preventDefault();
                alert(`Item ke-${i + 1} (${items[i].medicine_name}) jumlah harus lebih dari 0!`);
                return false;
            }
            if (items[i].unit_kemasan === 'box' && (!items[i].isi_per_box || items[i].isi_per_box <= 0)) {
                e.preventDefault();
                alert(`Item ke-${i + 1} (${items[i].medicine_name}) isi per box harus diisi jika kemasan adalah box!`);
                return false;
            }
        }
        
        // Pastikan total sudah terhitung
        calculateTotals();
        
        // Validasi tanggal jika jenis pembayaran tempo
        const jenisPembayaran = document.getElementById('jenis_pembayaran').value;
        if (jenisPembayaran === 'tempo') {
            const jatuhTempo = document.querySelector('input[name="jatuh_tempo"]').value;
            if (!jatuhTempo) {
                e.preventDefault();
                alert('Jatuh tempo harus diisi untuk pembayaran tempo!');
                return false;
            }
        }
        
        // Pastikan semua hidden input terisi dengan benar
        items.forEach((item, index) => {
            // Update hidden input untuk unit_jual
            const hiddenUnitJual = document.querySelector(`input[name="details[${item.id}][unit_jual]"]`);
            if (hiddenUnitJual) {
                hiddenUnitJual.value = item.unit_jual || '';
            } else {
                // Jika tidak ada, buat hidden input baru
                const form = document.getElementById('receiptForm');
                const newInput = document.createElement('input');
                newInput.type = 'hidden';
                newInput.name = `details[${item.id}][unit_jual]`;
                newInput.value = item.unit_jual || '';
                form.appendChild(newInput);
            }
            
            // Update hidden input untuk medicine_name
            const hiddenMedicineName = document.querySelector(`input[name="details[${item.id}][medicine_name]"]`);
            if (hiddenMedicineName) {
                hiddenMedicineName.value = item.medicine_name || '';
            } else {
                const form = document.getElementById('receiptForm');
                const newInput = document.createElement('input');
                newInput.type = 'hidden';
                newInput.name = `details[${item.id}][medicine_name]`;
                newInput.value = item.medicine_name || '';
                form.appendChild(newInput);
            }
            
            // Update hidden input untuk isi_per_box
            const hiddenIsiPerBox = document.querySelector(`input[name="details[${item.id}][isi_per_box]"]`);
            if (hiddenIsiPerBox) {
                hiddenIsiPerBox.value = item.isi_per_box || '';
            }
            
            // Update hidden input untuk description
            const hiddenDescription = document.querySelector(`input[name="details[${item.id}][description]"]`);
            if (hiddenDescription) {
                hiddenDescription.value = item.description || '';
            }
        });
    });
</script>
@endpush
@endsection
