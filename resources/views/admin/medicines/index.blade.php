@extends('layouts.admin')

@section('title', 'Inventory')
@section('header', 'Inventory')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6 flex justify-between items-center">
        <h3 class="text-xl font-semibold text-gray-800">Daftar Obat</h3>
        <div class="flex gap-2">
            <input type="text" id="searchMedicine" placeholder="Cari obat berdasarkan nama..." 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 w-64">
            <button type="button" onclick="resetSearch()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 hidden" id="resetBtn">
                <i class="fas fa-times mr-2"></i>Reset
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Form Upload Excel -->
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <h4 class="text-lg font-semibold text-gray-800 mb-3">
            <i class="fas fa-file-excel text-green-600 mr-2"></i>Upload Data Obat dari Excel
        </h4>
        <p class="text-sm text-gray-600 mb-3">
            Format Excel: <strong>Kolom B (Nama Obat)</strong> - Hanya membaca nama obat dari kolom kedua
        </p>
        <p class="text-xs text-gray-500 mb-3">
            File Excel dapat memiliki maksimal 4 sheet. Semua sheet akan diproses. Hanya kolom Nama Obat yang akan diimpor.
        </p>
        @php
            $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
        @endphp
        <form action="{{ route($routePrefix . '.medicines.import-excel') }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-xs text-gray-500">Format file: .xlsx atau .xls (maks. 10MB)</p>
            </div>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-upload mr-2"></i>Upload Excel
            </button>
        </form>
        <div class="mt-3 text-xs text-gray-500">
            <p><strong>Catatan:</strong></p>
            <ul class="list-disc list-inside mt-1">
                <li>Baris pertama akan diabaikan (header)</li>
                <li>Hanya membaca kolom <strong>Nama Obat</strong> (kolom B/kolom kedua)</li>
                <li>Nama Obat wajib diisi, baris tanpa nama akan diabaikan</li>
                <li>Jika nama obat sudah ada, akan diabaikan (tidak duplikat)</li>
                <li>Semua sheet dalam file Excel akan diproses (maksimal 4 sheet)</li>
                <li>Kode obat akan dibuat otomatis dari nama obat</li>
                <li>Data default: Harga = 0, Stok = 0, Unit = box (dapat diupdate manual setelah import)</li>
            </ul>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Obat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="medicineTableBody">
                @forelse($medicines as $medicine)
                    <tr class="medicine-row" 
                        data-name="{{ strtolower($medicine->name) }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $medicine->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $medicine->name }}</div>
                            @if($medicine->description)
                                <div class="text-xs text-gray-500">{{ Str::limit($medicine->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($medicine->price, 0, ',', '.') }} / {{ $medicine->unit }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $medicine->stock <= 10 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $medicine->stock }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $medicine->unit }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            @php
                                $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
                            @endphp
                            <a href="{{ route($routePrefix . '.medicines.show', $medicine) }}" class="text-green-600 hover:text-green-900" title="Detail">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            @if(auth()->check() && auth()->user()->role === 'admin')
                            <form action="{{ route('admin.medicines.destroy', $medicine) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus obat ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr id="emptyRow">
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada inventory</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@push('scripts')
<script>
// Search functionality - real-time filtering like cashier
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchMedicine');
    const resetBtn = document.getElementById('resetBtn');
    
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.medicine-row');
        const emptyRow = document.getElementById('emptyRow');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const name = row.dataset.name || '';
            
            // Check if search term matches name only
            if (name.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide empty message
        if (visibleCount === 0 && searchTerm !== '') {
            // Hide original empty row if exists
            if (emptyRow && emptyRow.textContent.includes('Belum ada inventory')) {
                emptyRow.style.display = 'none';
            }
            
            // Check if "not found" message already exists
            let notFoundRow = document.getElementById('notFoundRow');
            if (!notFoundRow) {
                const tbody = document.getElementById('medicineTableBody');
                notFoundRow = document.createElement('tr');
                notFoundRow.id = 'notFoundRow';
                notFoundRow.innerHTML = '<td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada obat ditemukan</td>';
                tbody.appendChild(notFoundRow);
            } else {
                notFoundRow.style.display = '';
            }
        } else {
            // Hide "not found" message
            const notFoundRow = document.getElementById('notFoundRow');
            if (notFoundRow) {
                notFoundRow.style.display = 'none';
            }
            
            // Show original empty row if no medicines at all
            if (emptyRow && rows.length === 0) {
                emptyRow.style.display = '';
            }
        }
        
        // Show/hide reset button
        if (searchTerm !== '') {
            resetBtn.classList.remove('hidden');
        } else {
            resetBtn.classList.add('hidden');
        }
    });
});

function resetSearch() {
    const searchInput = document.getElementById('searchMedicine');
    const rows = document.querySelectorAll('.medicine-row');
    const resetBtn = document.getElementById('resetBtn');
    const notFoundRow = document.getElementById('notFoundRow');
    const emptyRow = document.getElementById('emptyRow');
    
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Show all rows
    rows.forEach(row => {
        row.style.display = '';
    });
    
    // Hide reset button
    resetBtn.classList.add('hidden');
    
    // Hide "not found" message
    if (notFoundRow) {
        notFoundRow.style.display = 'none';
    }
    
    // Show original empty row if no medicines
    if (emptyRow && rows.length === 0) {
        emptyRow.style.display = '';
    }
}

</script>
@endpush
@endsection