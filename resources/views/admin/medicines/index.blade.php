@extends('layouts.admin')

@section('title', 'Inventory')
@section('header', 'Inventory')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6 flex justify-between items-center">
        <h3 class="text-xl font-semibold text-gray-800">Daftar Obat</h3>
        @php
            $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
        @endphp
        <form action="{{ route($routePrefix . '.medicines.import-excel') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <label for="excel_file" class="cursor-pointer">
                <div class="flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg hover:border-blue-400 transition-colors">
                    <i class="fas fa-file-excel text-green-600 mr-2"></i>
                    <span id="file-name" class="text-sm text-gray-600">Excel</span>
                    <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required class="hidden">
                </div>
            </label>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all font-medium text-sm">
                <i class="fas fa-upload mr-1"></i>Upload
            </button>
        </form>
    </div>

    <!-- Search Box -->
    <div class="mb-6 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex-1">
                <label for="searchMedicine" class="block mb-2 text-sm font-medium text-gray-700">
                    <i class="fas fa-search text-blue-600 mr-2"></i>Cari Obat
                </label>
                <div class="flex items-center gap-3">
                    <input type="text" id="searchMedicine" placeholder="Cari obat berdasarkan nama..." 
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                    <button type="button" onclick="resetSearch()" class="px-4 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 hidden" id="resetBtn">
                        <i class="fas fa-times mr-2"></i>Reset
                    </button>
                </div>
            </div>
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
// File input handler
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('excel_file');
    const fileName = document.getElementById('file-name');
    
    if (fileInput && fileName) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const fileNameText = e.target.files[0].name;
                // Tampilkan nama file yang lebih pendek jika terlalu panjang
                if (fileNameText.length > 20) {
                    fileName.textContent = fileNameText.substring(0, 17) + '...';
                } else {
                    fileName.textContent = fileNameText;
                }
                fileName.classList.remove('text-gray-600');
                fileName.classList.add('text-green-600', 'font-medium');
            } else {
                fileName.textContent = 'Excel';
                fileName.classList.remove('text-green-600', 'font-medium');
                fileName.classList.add('text-gray-600');
            }
        });
    }
});

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