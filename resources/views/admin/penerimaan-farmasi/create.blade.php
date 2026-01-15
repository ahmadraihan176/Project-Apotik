@extends('layouts.admin')

@section('title', 'Penerimaan Farmasi')
@section('header', 'Penerimaan Farmasi')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    @php
        $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
    @endphp
    <form action="{{ route($routePrefix . '.penerimaan-farmasi.store') }}" method="POST" id="receiptForm" novalidate>
        @csrf
        
        <!-- Informasi Umum Penerimaan -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Penerimaan</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Tanggal *</label>
                    <input type="date" name="receipt_date" id="receipt_date_input" value="{{ old('receipt_date', date('Y-m-d')) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 @error('receipt_date') border-red-500 @enderror"
                        onchange="updateNoUrut()">
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
                    <input type="text" name="no_urut" id="no_urut_input" value="{{ old('no_urut', $nextNoUrut ?? '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 bg-gray-50"
                        placeholder="Nomor urut" readonly>
                    <p class="text-xs text-gray-500 mt-1">Otomatis terisi per bulan</p>
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 bg-gray-50"
                        placeholder="Kode produk akan terisi otomatis" readonly>
                    <p class="text-xs text-gray-500 mt-1">Otomatis terisi saat input nama</p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Nama Barang *</label>
                    <input type="text" id="medicine_name_input" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Nama obat"
                        onblur="generateProductCode()">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Harga (Rp) *</label>
                    <input type="number" id="price_input" step="0.01" min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Harga per satuan pembelian"
                        title="Harga per satuan pembelian (misal: per box, per strip)"
                        onchange="calculateSellingPrice()"
                        onkeyup="calculateSellingPrice()">
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
                    <select id="unit_kemasan_select" onchange="toggleIsiPerBox(); calculateSellingPrice();"
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
                        placeholder="Jumlah strip/tablet per box"
                        onchange="calculateSellingPrice()"
                        onkeyup="calculateSellingPrice()">
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
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Margin (%) *</label>
                    <input type="number" id="margin_percent_input" step="1" min="0" value="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="0"
                        onchange="calculateSellingPrice()"
                        onkeyup="calculateSellingPrice()"
                        oninput="calculateSellingPrice()">
                    <p class="text-xs text-gray-500 mt-1">Persentase keuntungan</p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Harga Jual (Rp) *</label>
                    <input type="number" id="selling_price_input" step="1" min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 bg-gray-50"
                        placeholder="Otomatis terhitung"
                        readonly>
                    <p class="text-xs text-gray-500 mt-1">Harga jual per unit jual (otomatis)</p>
                </div>
                <div class="flex items-end">
                    <div class="flex gap-4 w-full">
                        <button type="button" onclick="addItem(); return false;" class="flex-1 px-4 py-2 gradient-bg text-white rounded-lg hover:opacity-90">
                            <i class="fas fa-plus mr-2"></i>Tambahkan
                        </button>
                        <button type="button" onclick="addItemAndContinue(); return false;" class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                            <i class="fas fa-plus-circle mr-2"></i>Tambahkan & Lanjutkan
                        </button>
                    </div>
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">MARGIN %</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">HARGA JUAL</th>
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
                        <input type="number" name="ppn_percent" id="ppn_percent" step="0.01" min="0" max="100" value="{{ old('ppn_percent', 11) }}"
                            class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            onchange="calculateTotals()" placeholder="%" readonly>
                        <div id="ppn_amount_display" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-800 flex items-center">
                            Rp 0
                        </div>
                        <input type="hidden" name="ppn_amount" id="ppn_amount" value="0">
                        <label class="flex items-center text-gray-700 font-semibold">PPN (11%)</label>
                    </div>
                    <div class="flex gap-2">
                        <div id="total_before_discount" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-800 flex items-center">
                            Rp 0
                        </div>
                        <label class="flex items-center text-gray-700 font-semibold">Total</label>
                    </div>
                    <div class="flex gap-2">
                        <div id="grand_total" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 font-bold text-lg text-gray-800 flex items-center">
                            Rp 0
                        </div>
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
    let generatedCodes = {}; // Simpan kode yang sudah di-generate untuk nama obat tertentu

    // Generate kode produk otomatis saat input nama obat
    function generateProductCode() {
        const medicineNameInput = document.getElementById('medicine_name_input');
        const productCodeInput = document.getElementById('product_code_input');
        const medicineName = medicineNameInput.value.trim();
        
        if (!medicineName) {
            productCodeInput.value = '';
            return;
        }
        
        // Jika sudah pernah generate untuk nama ini, gunakan kode yang sama
        if (generatedCodes[medicineName.toLowerCase()]) {
            productCodeInput.value = generatedCodes[medicineName.toLowerCase()];
            return;
        }
        
        // Generate kode baru: MED + 6 karakter random
        const randomChars = Math.random().toString(36).substring(2, 8).toUpperCase();
        const newCode = 'MED' + randomChars;
        
        // Simpan kode untuk nama ini
        generatedCodes[medicineName.toLowerCase()] = newCode;
        productCodeInput.value = newCode;
    }

    // Update nomor urut saat tanggal berubah
    function updateNoUrut() {
        const receiptDateInput = document.getElementById('receipt_date_input');
        const noUrutInput = document.getElementById('no_urut_input');
        
        if (receiptDateInput && receiptDateInput.value) {
            // Request nomor urut dari server berdasarkan tanggal
            fetch('{{ route($routePrefix . ".penerimaan-farmasi.get-no-urut") }}?date=' + receiptDateInput.value, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.no_urut) {
                    noUrutInput.value = data.no_urut;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }

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
        const marginPercentInput = document.getElementById('margin_percent_input');
        const sellingPriceInput = document.getElementById('selling_price_input');
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
        let marginPercent = parseFloat(marginPercentInput.value) || 0;
        let sellingPrice = parseFloat(sellingPriceInput.value) || 0;
        const itemSubtotal = price * quantity;
        const discountAmount = itemSubtotal * discountPercent / 100;
        
        // Pastikan margin dan selling_price terhitung dengan benar
        // Hitung harga setelah diskon
        const priceAfterDiscount = price - (price * discountPercent / 100);
        
        // Hitung PPN 11%
        const ppnAmount = priceAfterDiscount * 0.11;
        const priceWithPPN = priceAfterDiscount + ppnAmount;
        
        // Hitung harga jual dengan margin
        const priceWithMargin = priceWithPPN * (1 + marginPercent / 100);
        
        // Jika kemasan = box dan ada isi per box, hitung harga per unit jual
        if (unitKemasan === 'box' && isiPerBox && isiPerBox > 0) {
            sellingPrice = priceWithMargin / isiPerBox;
        } else {
            sellingPrice = priceWithMargin;
        }
        
        // Bulatkan ke bilangan bulat tanpa desimal
        marginPercent = Math.round(marginPercent);
        sellingPrice = Math.round(sellingPrice);
        
        // Update input field juga untuk memastikan konsistensi
        marginPercentInput.value = marginPercent;
        sellingPriceInput.value = sellingPrice;

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
        if (!marginPercent || marginPercent <= 0) {
            alert('Margin % harus diisi dan lebih dari 0!');
            return;
        }
        if (!sellingPrice || sellingPrice <= 0) {
            alert('Harga jual harus terhitung! Pastikan semua field sudah diisi dengan benar.');
            return;
        }

        itemCount++;
        // Pastikan nilai margin dan selling_price sudah terhitung dengan benar
        const finalMarginPercent = Math.round(marginPercent);
        const finalSellingPrice = Math.round(sellingPrice);
        
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
            margin_percent: finalMarginPercent,
            selling_price: finalSellingPrice,
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
        marginPercentInput.value = 0;
        sellingPriceInput.value = '';
        toggleIsiPerBox();
        
        // Focus ke nama barang untuk input berikutnya
        medicineNameInput.focus();
    }

    function addItemAndContinue(event) {
        // Prevent any form submission
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
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
        const marginPercentInput = document.getElementById('margin_percent_input');
        const sellingPriceInput = document.getElementById('selling_price_input');
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
        let marginPercent = parseFloat(marginPercentInput.value) || 0;
        let sellingPrice = parseFloat(sellingPriceInput.value) || 0;
        const itemSubtotal = price * quantity;
        const discountAmount = itemSubtotal * discountPercent / 100;
        
        // Pastikan margin dan selling_price terhitung dengan benar
        // Hitung harga setelah diskon
        const priceAfterDiscount = price - (price * discountPercent / 100);
        
        // Hitung PPN 11%
        const ppnAmount = priceAfterDiscount * 0.11;
        const priceWithPPN = priceAfterDiscount + ppnAmount;
        
        // Hitung harga jual dengan margin
        const priceWithMargin = priceWithPPN * (1 + marginPercent / 100);
        
        // Jika kemasan = box dan ada isi per box, hitung harga per unit jual
        if (unitKemasan === 'box' && isiPerBox && isiPerBox > 0) {
            sellingPrice = priceWithMargin / isiPerBox;
        } else {
            sellingPrice = priceWithMargin;
        }
        
        // Bulatkan ke bilangan bulat tanpa desimal
        marginPercent = Math.round(marginPercent);
        sellingPrice = Math.round(sellingPrice);
        
        // Update input field juga untuk memastikan konsistensi
        marginPercentInput.value = marginPercent;
        sellingPriceInput.value = sellingPrice;

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
        if (!marginPercent || marginPercent <= 0) {
            alert('Margin % harus diisi dan lebih dari 0!');
            return;
        }
        if (!sellingPrice || sellingPrice <= 0) {
            alert('Harga jual harus terhitung! Pastikan semua field sudah diisi dengan benar.');
            return;
        }

        itemCount++;
        // Pastikan nilai margin dan selling_price sudah terhitung dengan benar
        const finalMarginPercent = Math.round(marginPercent);
        const finalSellingPrice = Math.round(sellingPrice);
        
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
            margin_percent: finalMarginPercent,
            selling_price: finalSellingPrice,
            no_batch: noBatch,
            expired_date: expiredDate
        };

        items.push(item);
        renderItemsTable();
        
        // Setelah menambahkan item, kosongkan field informasi umum untuk input faktur berikutnya
        // Tanggal tetap diisi dengan tanggal hari ini (required)
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('input[name="receipt_date"]').value = today;
        
        // Supplier dikosongkan
        document.getElementById('supplier_name').value = '';
        
        // Jenis Penerimaan dikosongkan
        document.querySelector('select[name="jenis_penerimaan"]').value = '';
        
        // No. SP dikosongkan
        document.querySelector('input[name="no_sp"]').value = '';
        
        // No. Faktur dikosongkan
        document.querySelector('input[name="no_faktur"]').value = '';
        
        // Jenis Pembayaran direset ke cash (required)
        document.getElementById('jenis_pembayaran').value = 'cash';
        
        // Jatuh Tempo dikosongkan
        document.querySelector('input[name="jatuh_tempo"]').value = '';
        
        // Diterima Semua dikosongkan
        document.querySelector('select[name="diterima_semua"]').value = '';
        
        // No. Urut dikosongkan
        document.querySelector('input[name="no_urut"]').value = '';
        
        // Catatan dikosongkan
        document.querySelector('textarea[name="notes"]').value = '';
        
        // Update toggle jatuh tempo
        toggleJatuhTempo();
        
        // Reset form input barang untuk input berikutnya (reset semua termasuk unit)
        productCodeInput.value = '';
        generatedCodes = {}; // Reset generated codes
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
        marginPercentInput.value = 0;
        sellingPriceInput.value = '';
        toggleIsiPerBox();
        
        // Reset kode produk setelah item ditambahkan
        productCodeInput.value = '';
        productCodeInput.readOnly = true;
        
        // Focus ke nama barang untuk input berikutnya
        medicineNameInput.focus();
        
        // Scroll ke bagian daftar barang untuk melihat item yang sudah ditambahkan
        document.getElementById('itemsTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Pastikan tidak ada form submission
        return false;
    }

    function calculateItemDiscount() {
        // Fungsi ini tetap ada untuk kompatibilitas, tapi tidak perlu melakukan apa-apa
        // karena diskon hanya dihitung dari persen saat addItem
        calculateSellingPrice();
    }

    function calculateSellingPrice() {
        const priceInput = document.getElementById('price_input');
        const discountPercentInput = document.getElementById('discount_percent_input');
        const marginPercentInput = document.getElementById('margin_percent_input');
        const sellingPriceInput = document.getElementById('selling_price_input');
        const unitKemasanSelect = document.getElementById('unit_kemasan_select');
        const isiPerBoxInput = document.getElementById('isi_per_box_input');
        const unitJualSelect = document.getElementById('unit_jual_select');
        
        const price = parseFloat(priceInput.value) || 0;
        const discountPercent = parseFloat(discountPercentInput.value) || 0;
        const marginPercent = parseFloat(marginPercentInput.value) || 0;
        const unitKemasan = unitKemasanSelect.value;
        const isiPerBox = unitKemasan === 'box' ? parseInt(isiPerBoxInput.value) || 0 : null;
        const unitJual = unitJualSelect.value;
        
        if (!price || price <= 0) {
            sellingPriceInput.value = '';
            return;
        }
        
        // Hitung harga setelah diskon
        const priceAfterDiscount = price - (price * discountPercent / 100);
        
        // Hitung PPN 11%
        const ppnAmount = priceAfterDiscount * 0.11;
        const priceWithPPN = priceAfterDiscount + ppnAmount;
        
        // Hitung harga jual dengan margin
        const priceWithMargin = priceWithPPN * (1 + marginPercent / 100);
        
        // Jika kemasan = box dan ada isi per box, hitung harga per unit jual
        let sellingPrice = priceWithMargin;
        if (unitKemasan === 'box' && isiPerBox && isiPerBox > 0) {
            sellingPrice = priceWithMargin / isiPerBox;
        }
        
        // Bulatkan ke bilangan bulat tanpa desimal
        sellingPriceInput.value = Math.round(sellingPrice);
    }

    function removeItem(itemId) {
        items = items.filter(item => item.id !== itemId);
        renderItemsTable();
        calculateTotals();
    }

    function editItem(itemId) {
        const item = items.find(i => i.id === itemId);
        if (item) {
            // Isi form dengan data item yang akan diedit
            document.getElementById('product_code_input').value = item.product_code || '';
            document.getElementById('product_code_input').readOnly = true; // Tetap readonly
            document.getElementById('medicine_name_input').value = item.medicine_name || '';
            // Generate kode jika belum ada
            if (!item.product_code) {
                generateProductCode();
            }
            document.getElementById('price_input').value = item.price || '';
            document.getElementById('unit_jual_select').value = item.unit_jual || '';
            document.getElementById('unit_kemasan_select').value = item.unit_kemasan || '';
            document.getElementById('isi_per_box_input').value = item.isi_per_box || '';
            document.getElementById('quantity_input').value = item.quantity || 1;
            document.getElementById('description_input').value = item.description || '';
            document.getElementById('no_batch_input').value = item.no_batch || '';
            document.getElementById('expired_date_input').value = item.expired_date || '';
            document.getElementById('discount_percent_input').value = item.discount_percent || 0;
            document.getElementById('margin_percent_input').value = item.margin_percent || 0;
            document.getElementById('selling_price_input').value = item.selling_price || 0;
            
            // Toggle isi per box jika perlu
            toggleIsiPerBox();
            
            // Hapus item dari array (akan diganti dengan yang baru setelah di-edit)
            items = items.filter(i => i.id !== itemId);
            renderItemsTable();
            
            // Scroll ke form input
            document.getElementById('product_code_input').scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById('medicine_name_input').focus();
        }
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
                margin_percent: item.margin_percent,
                selling_price: item.selling_price,
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
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <input type="number" name="details[${item.id}][margin_percent]" value="${Math.round(item.margin_percent || 0)}" step="1" min="0"
                        class="w-20 px-2 py-1 border border-gray-300 rounded" 
                        onchange="updateItemMargin(${item.id}, this.value)"
                        onkeyup="updateItemMargin(${item.id}, this.value)">
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <span class="text-gray-700 font-medium">Rp ${formatRupiah(item.selling_price || 0)}</span>
                    <span class="text-xs text-gray-500">/${item.unit_jual || ''}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold">${formatCurrency(subtotal)}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <div class="flex gap-2">
                        <button type="button" onclick="editItem(${item.id})" class="text-green-600 hover:text-green-800" title="Edit item">
                            <i class="fas fa-edit"></i>
                        </button>
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
                <input type="hidden" name="details[${item.id}][unit_kemasan]" value="${item.unit_kemasan || ''}">
                <input type="hidden" name="details[${item.id}][isi_per_box]" value="${item.isi_per_box || ''}">
                <input type="hidden" name="details[${item.id}][description]" value="${item.description || ''}">
                <input type="hidden" name="details[${item.id}][no_batch]" value="${item.no_batch || ''}">
                <input type="hidden" name="details[${item.id}][expired_date]" value="${item.expired_date || ''}">
                <input type="hidden" name="details[${item.id}][quantity]" value="${item.quantity}">
                <input type="hidden" name="details[${item.id}][price]" value="${item.price}">
                <input type="hidden" name="details[${item.id}][discount_percent]" value="${item.discount_percent || 0}">
                <input type="hidden" name="details[${item.id}][discount_amount]" value="${item.discount_amount || 0}">
                <input type="hidden" name="details[${item.id}][margin_percent]" value="${Math.round(item.margin_percent || 0)}">
                <input type="hidden" name="details[${item.id}][selling_price]" value="${Math.round(item.selling_price || 0)}">
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
            recalculateItemSellingPrice(item);
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
            // Recalculate selling price when discount changes
            recalculateItemSellingPrice(item);
            renderItemsTable();
        }
    }

    function updateItemMargin(itemId, value) {
        const item = items.find(i => i.id === itemId);
        if (item) {
            // Pastikan margin bisa decimal
            item.margin_percent = parseFloat(value) || 0;
            // Recalculate harga jual saat margin berubah
            recalculateItemSellingPrice(item);
            renderItemsTable();
        }
    }

    function recalculateItemSellingPrice(item) {
        if (!item.price || item.price <= 0) return;
        
        // Hitung harga setelah diskon
        const priceAfterDiscount = item.price - (item.price * (item.discount_percent || 0) / 100);
        
        // Hitung PPN 11%
        const ppnAmount = priceAfterDiscount * 0.11;
        const priceWithPPN = priceAfterDiscount + ppnAmount;
        
        // Hitung harga jual dengan margin
        const marginPercent = item.margin_percent || 0;
        const priceWithMargin = priceWithPPN * (1 + marginPercent / 100);
        
        // Jika kemasan = box dan ada isi per box, hitung harga per unit jual
        let sellingPrice = priceWithMargin;
        if (item.unit_kemasan === 'box' && item.isi_per_box && item.isi_per_box > 0) {
            sellingPrice = priceWithMargin / item.isi_per_box;
        }
        
        // Bulatkan ke bilangan bulat tanpa desimal
        item.selling_price = Math.round(sellingPrice);
    }

    function calculateTotals() {
        // Hitung total dari items (setelah diskon per item)
        let subtotal = 0;
        items.forEach(item => {
            const itemSubtotal = (item.price * item.quantity) - item.discount_amount;
            subtotal += itemSubtotal;
        });

        // Tampilkan Total dengan format currency
        document.getElementById('total_before_discount').textContent = formatCurrency(subtotal);

        // Hitung PPN (otomatis 11%) dari subtotal
        const ppnPercent = 11; // PPN selalu 11%
        const ppnAmountInput = document.getElementById('ppn_amount');
        const ppnAmountDisplay = document.getElementById('ppn_amount_display');
        const finalPPN = subtotal * ppnPercent / 100;
        ppnAmountInput.value = finalPPN.toFixed(2);
        ppnAmountDisplay.textContent = formatCurrency(finalPPN);
        
        // Grand total = subtotal + PPN
        const grandTotal = subtotal + finalPPN;
        document.getElementById('grand_total').textContent = formatCurrency(grandTotal);
    }

    function formatCurrency(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    function formatRupiah(amount) {
        if (!amount || amount === 0) return '0';
        // Bulatkan dulu, lalu format dengan titik pemisah ribuan
        const rounded = Math.round(parseFloat(amount));
        return rounded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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
        
        // Pastikan semua hidden input terisi dengan benar dari array items
        items.forEach((item) => {
            // Update semua hidden input dengan nilai terbaru dari array items
            const fields = [
                'medicine_name', 'product_code', 'unit_jual', 'unit_kemasan', 
                'isi_per_box', 'description', 'no_batch', 'expired_date',
                'quantity', 'price', 'discount_percent', 'discount_amount'
            ];
            
            fields.forEach(field => {
                const hiddenInput = document.querySelector(`input[name="details[${item.id}][${field}]"]`);
                if (hiddenInput) {
                    if (field === 'quantity') {
                        hiddenInput.value = item.quantity || 1;
                    } else if (field === 'price') {
                        hiddenInput.value = item.price || 0;
                    } else if (field === 'discount_percent' || field === 'discount_amount') {
                        hiddenInput.value = item[field] || 0;
                    } else {
                        hiddenInput.value = item[field] || '';
                    }
                }
            });
        });
    });
</script>
@endpush
@endsection
