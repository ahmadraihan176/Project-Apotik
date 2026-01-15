@extends('layouts.admin')

@section('title', 'Rekapan Presensi Bulanan')
@section('header', 'Rekapan Presensi Bulanan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold text-gray-800">Rekapan Presensi Per Bulan</h3>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.presensi.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
            <form method="GET" action="{{ route('admin.presensi.rekapan') }}" class="flex items-center space-x-2">
                <select name="bulan" id="bulanSelect" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @php
                        $tahunDipilih = $tahun;
                        // Jika tahun sama dengan tahun pertama presensi, mulai dari bulan pertama presensi
                        // Jika tidak, mulai dari Januari
                        $bulanMulai = ($tahunDipilih == $tahunPertamaPresensi) ? $bulanPertamaPresensi : 1;
                    @endphp
                    @for($i = $bulanMulai; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->locale('id')->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select name="tahun" id="tahunSelect" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </form>
        </div>
    </div>

    <!-- Info Bulan -->
    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
        <p class="text-lg font-semibold text-gray-800">
            <i class="fas fa-calendar-alt mr-2"></i>
            Rekapan Bulan: {{ \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y') }}
        </p>
        <p class="text-sm text-gray-600 mt-1">
            Total Hari: {{ \Carbon\Carbon::create($tahun, $bulan, 1)->daysInMonth }} hari
        </p>
    </div>

    <!-- Tabel Rekapan -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Hadir</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Hari</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Persentase</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rekapan as $index => $r)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $r['karyawan']->nik }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $r['karyawan']->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                            <span class="font-semibold">{{ $r['total_hadir'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                            {{ $r['total_hari'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            <div class="flex items-center justify-center">
                                <div class="w-32 bg-gray-200 rounded-full h-4 mr-2">
                                    <div class="bg-sky-600 h-4 rounded-full" style="width: {{ min($r['persentase'], 100) }}%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-700">{{ $r['persentase'] }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($r['persentase'] >= 80)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Baik
                                </span>
                            @elseif($r['persentase'] >= 60)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-circle mr-1"></i>Cukup
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>Kurang
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data karyawan untuk rekapan bulan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Statistik Rekapan -->
    @if(count($rekapan) > 0)
        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Karyawan</p>
                        <p class="text-2xl font-bold text-blue-600">{{ count($rekapan) }}</p>
                    </div>
                    <i class="fas fa-users text-3xl text-blue-400"></i>
                </div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Rata-rata Kehadiran</p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ number_format(collect($rekapan)->avg('persentase'), 2) }}%
                        </p>
                    </div>
                    <i class="fas fa-chart-line text-3xl text-green-400"></i>
                </div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Karyawan dengan Kehadiran ≥80%</p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ collect($rekapan)->where('persentase', '>=', 80)->count() }}
                        </p>
                    </div>
                    <i class="fas fa-star text-3xl text-purple-400"></i>
                </div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Karyawan dengan Kehadiran <60%</p>
                        <p class="text-2xl font-bold text-orange-600">
                            {{ collect($rekapan)->where('persentase', '<', 60)->count() }}
                        </p>
                    </div>
                    <i class="fas fa-exclamation-triangle text-3xl text-orange-400"></i>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Update opsi bulan ketika tahun berubah
    document.getElementById('tahunSelect').addEventListener('change', function() {
        const tahunDipilih = parseInt(this.value);
        const bulanSelect = document.getElementById('bulanSelect');
        const bulanSekarang = parseInt(bulanSelect.value);
        
        // Hapus semua opsi bulan
        bulanSelect.innerHTML = '';
        
        // Jika tahun sama dengan tahun pertama presensi, mulai dari bulan pertama presensi
        // Jika tidak, mulai dari Januari (1)
        const tahunPertamaPresensi = {{ $tahunPertamaPresensi }};
        const bulanPertamaPresensi = {{ $bulanPertamaPresensi }};
        const bulanMulai = (tahunDipilih == tahunPertamaPresensi) ? bulanPertamaPresensi : 1;
        
        // Buat opsi bulan
        const namaBulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        for (let i = bulanMulai; i <= 12; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = namaBulan[i - 1];
            
            // Jika bulan yang dipilih masih valid, set sebagai selected
            if (i == bulanSekarang && i >= bulanMulai) {
                option.selected = true;
            } else if (i == bulanMulai && bulanSekarang < bulanMulai) {
                option.selected = true;
            }
            
            bulanSelect.appendChild(option);
        }
    });
</script>
@endpush
@endsection

