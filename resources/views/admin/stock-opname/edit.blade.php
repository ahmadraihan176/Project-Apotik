@extends('layouts.admin')

@section('title', 'Edit Stok Opname')
@section('header', 'Edit Stok Opname')

@section('content')
@if($stockOpname->isApproved())
<div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded-lg">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    <strong>Peringatan:</strong> Opname ini sudah disetujui. Update akan:
    <ul class="list-disc list-inside mt-2 ml-4">
        <li>Mengembalikan stok ke kondisi sebelum approval</li>
        <li>Mereset status approval</li>
        <li>Memerlukan approval ulang setelah update</li>
    </ul>
</div>
@endif

<div class="bg-white rounded-lg shadow-md p-6">
    <form action="{{ route('admin.stock-opname.update', $stockOpname) }}" method="POST" id="opnameForm">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Tanggal Opname *</label>
                <input type="date" name="opname_date" value="{{ old('opname_date', $stockOpname->opname_date->format('Y-m-d')) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 @error('opname_date') border-red-500 @enderror">
                @error('opname_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Catatan</label>
                <input type="text" name="notes" value="{{ old('notes', $stockOpname->notes) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                    placeholder="Catatan umum opname">
            </div>
        </div>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Detail Obat</h3>
                <button type="button" onclick="addMedicineRow()" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-plus mr-2"></i>Tambah Obat
                </button>
            </div>

            <div id="medicineRows" class="space-y-4">
                <!-- Existing rows will be loaded here -->
            </div>
        </div>

        <div class="flex space-x-4">
            <button type="submit" name="save_as_draft" value="0" class="flex-1 gradient-bg text-white font-semibold py-2 rounded-lg hover:opacity-90">
                <i class="fas fa-save mr-2"></i>Update & Selesai
            </button>
            <button type="submit" name="save_as_draft" value="1" class="flex-1 bg-yellow-500 text-white font-semibold py-2 rounded-lg hover:bg-yellow-600">
                <i class="fas fa-file-alt mr-2"></i>Update sebagai Draft
            </button>
            <a href="{{ route('admin.stock-opname.index') }}" class="flex-1 text-center bg-gray-300 text-gray-700 font-semibold py-2 rounded-lg hover:bg-gray-400">
                <i class="fas fa-times mr-2"></i>Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const medicines = @json($medicines);
    const existingDetails = @json($stockOpname->details);
    let rowCount = 0;

    function addMedicineRow(medicineData = null) {
        rowCount++;
        const row = document.createElement('div');
        row.className = 'border border-gray-300 rounded-lg p-4 bg-gray-50';
        row.id = `row-${rowCount}`;
        
        const medicineId = medicineData ? medicineData.medicine_id : '';
        const medicine = medicines.find(m => m.id == medicineId);
        const systemStock = medicine ? medicine.stock : 0;
        
        row.innerHTML = `
            <div class="flex justify-between items-start mb-4">
                <h4 class="font-semibold text-gray-700">Item #${rowCount}</h4>
                <button type="button" onclick="removeRow(${rowCount})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Obat *</label>
                    <select name="details[${rowCount}][medicine_id]" required onchange="updateSystemStock(${rowCount}, this.value)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Obat</option>
                        ${medicines.map(m => `<option value="${m.id}" ${m.id == medicineId ? 'selected' : ''}>${m.name} (Stok: ${m.stock} ${m.unit})</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Stok Sistem</label>
                    <input type="number" id="system_stock_${rowCount}" value="${systemStock}" readonly
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Nomor Batch</label>
                    <input type="text" name="details[${rowCount}][batch_number]" value="${medicineData?.batch_number || ''}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Masukkan nomor batch">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Tanggal Kadaluarsa</label>
                    <input type="date" name="details[${rowCount}][expired_date]" value="${medicineData?.expired_date || ''}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Kondisi *</label>
                    <select name="details[${rowCount}][condition]" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="baik" ${medicineData?.condition === 'baik' ? 'selected' : ''}>Baik</option>
                        <option value="rusak" ${medicineData?.condition === 'rusak' ? 'selected' : ''}>Rusak</option>
                        <option value="kadaluarsa" ${medicineData?.condition === 'kadaluarsa' ? 'selected' : ''}>Kadaluarsa</option>
                        <option value="hampir_kadaluarsa" ${medicineData?.condition === 'hampir_kadaluarsa' ? 'selected' : ''}>Hampir Kadaluarsa</option>
                        <option value="retur" ${medicineData?.condition === 'retur' ? 'selected' : ''}>Retur</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Stok Fisik *</label>
                    <input type="number" name="details[${rowCount}][physical_stock]" value="${medicineData?.physical_stock || ''}" required min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Masukkan stok fisik">
                </div>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2 text-sm">Keterangan</label>
                <textarea name="details[${rowCount}][notes]" rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                    placeholder="Catatan tambahan">${medicineData?.notes || ''}</textarea>
            </div>
        `;
        
        document.getElementById('medicineRows').appendChild(row);
    }

    function removeRow(id) {
        document.getElementById(`row-${id}`).remove();
    }

    function updateSystemStock(rowId, medicineId) {
        const medicine = medicines.find(m => m.id == medicineId);
        if (medicine) {
            document.getElementById(`system_stock_${rowId}`).value = medicine.stock;
        }
    }

    // Load existing details on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (existingDetails.length > 0) {
            existingDetails.forEach(detail => {
                addMedicineRow({
                    medicine_id: detail.medicine_id,
                    batch_number: detail.batch_number,
                    expired_date: detail.expired_date ? detail.expired_date.split('T')[0] : '',
                    condition: detail.condition,
                    physical_stock: detail.physical_stock,
                    notes: detail.notes
                });
            });
        } else {
            addMedicineRow();
        }
    });
</script>
@endpush
@endsection

