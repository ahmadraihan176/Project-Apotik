@extends('layouts.admin')

@section('title', 'Tambah Stok Opname')
@section('header', 'Tambah Stok Opname Baru')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    @php
        $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
    @endphp
    <form action="{{ route($routePrefix . '.stock-opname.store') }}" method="POST" id="opnameForm">
        @csrf
        
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Tanggal Opname *</label>
                <input type="date" name="opname_date" value="{{ old('opname_date', date('Y-m-d')) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 @error('opname_date') border-red-500 @enderror">
                @error('opname_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Catatan</label>
                <input type="text" name="notes" value="{{ old('notes') }}"
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
                <!-- Rows will be added here dynamically -->
            </div>
        </div>

        <div class="flex space-x-4">
            <button type="submit" name="save_as_draft" value="0" class="flex-1 gradient-bg text-white font-semibold py-2 rounded-lg hover:opacity-90">
                <i class="fas fa-save mr-2"></i>Simpan & Selesai
            </button>
            <button type="submit" name="save_as_draft" value="1" class="flex-1 bg-yellow-500 text-white font-semibold py-2 rounded-lg hover:bg-yellow-600">
                <i class="fas fa-file-alt mr-2"></i>Simpan sebagai Draft
            </button>
            @php
                $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
            @endphp
            <a href="{{ route($routePrefix . '.stock-opname.index') }}" class="flex-1 text-center bg-gray-300 text-gray-700 font-semibold py-2 rounded-lg hover:bg-gray-400">
                <i class="fas fa-times mr-2"></i>Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const medicines = @json($medicines);
    let rowCount = 0;

    function addMedicineRow(medicineData = null) {
        rowCount++;
        const row = document.createElement('div');
        row.className = 'border border-gray-300 rounded-lg p-4 bg-gray-50';
        row.id = `row-${rowCount}`;
        
        const medicineId = medicineData ? medicineData.id : '';
        const medicineName = medicineData ? medicineData.name : '';
        const systemStock = medicineData ? medicineData.stock : 0;
        
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
                    <div class="relative">
                        <input type="text" 
                            id="medicine_search_${rowCount}" 
                            placeholder="Cari obat..." 
                            autocomplete="off"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                            oninput="searchMedicine(${rowCount}, this.value)"
                            onfocus="showMedicineDropdown(${rowCount})"
                            onblur="setTimeout(() => hideMedicineDropdown(${rowCount}), 200)">
                        <input type="hidden" name="details[${rowCount}][medicine_id]" id="medicine_id_${rowCount}" required>
                        <div id="medicine_dropdown_${rowCount}" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <!-- Dropdown items will be inserted here -->
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Stok Sistem</label>
                    <input type="number" id="system_stock_${rowCount}" value="${systemStock}" readonly
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Nomor Batch <span class="text-xs text-gray-500">(otomatis dari penerimaan)</span></label>
                    <input type="text" name="details[${rowCount}][batch_number]" id="batch_number_${rowCount}" value="${medicineData?.batch_number || ''}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Akan terisi otomatis saat memilih obat">
                    <div id="batch_status_${rowCount}" class="text-xs mt-1 hidden"></div>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Tanggal Kadaluarsa</label>
                    <input type="date" name="details[${rowCount}][expired_date]" id="expired_date_${rowCount}" value="${medicineData?.expired_date || ''}"
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
            
            // Auto-fill batch number and expired date from penerimaan farmasi
            const batchInput = document.getElementById(`batch_number_${rowId}`);
            const expiredInput = document.getElementById(`expired_date_${rowId}`);
            const batchStatus = document.getElementById(`batch_status_${rowId}`);
            
            if (batchInput) {
                batchInput.value = ''; // Clear first
                batchInput.style.backgroundColor = '#fef3c7'; // Yellow while loading
            }
            
            if (batchStatus) {
                batchStatus.textContent = 'Mengambil data...';
                batchStatus.classList.remove('hidden');
                batchStatus.classList.remove('text-green-600', 'text-red-600');
                batchStatus.classList.add('text-blue-600');
            }
            
            @php
                $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
            @endphp
            const url = `{{ route($routePrefix . '.stock-opname.get-medicine-batch') }}?medicine_id=${medicineId}`;
            console.log('Fetching batch data from:', url); // Debug log
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    console.log('Response status:', response.status); // Debug log
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Network response was not ok');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Batch data received:', data); // Debug log
                    
                    if (data.success && data.batch_number && batchInput) {
                        batchInput.value = data.batch_number;
                        batchInput.style.backgroundColor = '#d1fae5'; // Light green to show it's auto-filled
                        setTimeout(() => {
                            batchInput.style.backgroundColor = '';
                        }, 3000);
                        
                        if (batchStatus) {
                            batchStatus.textContent = '✓ Nomor batch berhasil diisi otomatis';
                            batchStatus.classList.remove('text-blue-600', 'text-red-600');
                            batchStatus.classList.add('text-green-600');
                        }
                    } else {
                        if (batchInput) {
                            batchInput.style.backgroundColor = '';
                        }
                        if (batchStatus) {
                            const message = data.message || 'Nomor batch tidak ditemukan di penerimaan farmasi';
                            batchStatus.textContent = `⚠ ${message}`;
                            batchStatus.classList.remove('text-blue-600', 'text-green-600');
                            batchStatus.classList.add('text-red-600');
                        }
                    }
                    
                    if (data.expired_date && expiredInput) {
                        expiredInput.value = data.expired_date;
                        expiredInput.style.backgroundColor = '#d1fae5'; // Light green to show it's auto-filled
                        setTimeout(() => {
                            expiredInput.style.backgroundColor = '';
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error fetching batch number:', error);
                    if (batchInput) {
                        batchInput.style.backgroundColor = '';
                    }
                    if (batchStatus) {
                        batchStatus.textContent = `✗ Error: ${error.message || 'Gagal mengambil data batch'}`;
                        batchStatus.classList.remove('text-blue-600', 'text-green-600');
                        batchStatus.classList.add('text-red-600');
                    }
                });
        }
    }

    function searchMedicine(rowId, searchTerm) {
        const dropdown = document.getElementById(`medicine_dropdown_${rowId}`);
        const searchLower = searchTerm.toLowerCase().trim();
        
        if (searchTerm.length === 0) {
            showMedicineDropdown(rowId);
            return;
        }
        
        const filtered = medicines.filter(m => 
            m.name.toLowerCase().includes(searchLower)
        );
        
        if (filtered.length === 0) {
            dropdown.innerHTML = '<div class="px-4 py-2 text-gray-500 text-sm">Tidak ada obat ditemukan</div>';
            dropdown.classList.remove('hidden');
            return;
        }
        
        dropdown.innerHTML = filtered.map(m => {
            const escapedName = m.name.replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, ' ');
            return `
                <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-200" 
                     onclick="selectMedicine(${rowId}, ${m.id}, '${escapedName}', ${m.stock})">
                    <div class="font-semibold text-gray-800">${m.name}</div>
                    <div class="text-xs text-gray-500">Stok: ${m.stock} ${m.unit}</div>
                </div>
            `;
        }).join('');
        
        dropdown.classList.remove('hidden');
    }

    function showMedicineDropdown(rowId) {
        const dropdown = document.getElementById(`medicine_dropdown_${rowId}`);
        const searchInput = document.getElementById(`medicine_search_${rowId}`);
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        if (searchTerm.length === 0) {
            // Show all medicines
            dropdown.innerHTML = medicines.map(m => {
                const escapedName = m.name.replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, ' ');
                return `
                    <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-200" 
                         onclick="selectMedicine(${rowId}, ${m.id}, '${escapedName}', ${m.stock})">
                        <div class="font-semibold text-gray-800">${m.name}</div>
                        <div class="text-xs text-gray-500">Stok: ${m.stock} ${m.unit}</div>
                    </div>
                `;
            }).join('');
        } else {
            searchMedicine(rowId, searchTerm);
        }
        
        dropdown.classList.remove('hidden');
    }

    function hideMedicineDropdown(rowId) {
        const dropdown = document.getElementById(`medicine_dropdown_${rowId}`);
        dropdown.classList.add('hidden');
    }

    function selectMedicine(rowId, medicineId, medicineName, stock) {
        const searchInput = document.getElementById(`medicine_search_${rowId}`);
        const medicineIdInput = document.getElementById(`medicine_id_${rowId}`);
        const dropdown = document.getElementById(`medicine_dropdown_${rowId}`);
        
        searchInput.value = medicineName;
        medicineIdInput.value = medicineId;
        dropdown.classList.add('hidden');
        
        // Update system stock and fetch batch number
        // Ensure medicineId is a number
        const medicineIdNum = parseInt(medicineId);
        console.log('Selected medicine:', { rowId, medicineId, medicineIdNum, medicineName }); // Debug
        updateSystemStock(rowId, medicineIdNum);
    }

    // Add first row on page load
    document.addEventListener('DOMContentLoaded', function() {
        addMedicineRow();
    });
</script>
@endpush
@endsection

