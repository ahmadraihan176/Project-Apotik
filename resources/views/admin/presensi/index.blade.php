@extends('layouts.admin')

@section('title', 'Manajemen Presensi Karyawan')
@section('header', 'Manajemen Presensi Karyawan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold text-gray-800">Daftar Presensi Karyawan</h3>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.presensi.rekapan') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                <i class="fas fa-chart-bar mr-2"></i>Rekapan Bulanan
            </a>
            <form method="GET" action="{{ route('admin.presensi.index') }}" class="flex items-center space-x-2">
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                <button type="submit" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                @if(request('tanggal'))
                    <a href="{{ route('admin.presensi.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        <i class="fas fa-times mr-2"></i>Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam Masuk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($presensi as $p)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $p->user->nik ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $p->user->name ?? $p->nama }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $p->jam_masuk ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($p->status)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Hadir
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>Tidak Hadir
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            @if(request('tanggal'))
                                Tidak ada data presensi pada tanggal yang dipilih.
                            @else
                                Belum ada data presensi.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $presensi->links() }}
    </div>

    <!-- Statistik -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Presensi</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $presensi->total() }}</p>
                </div>
                <i class="fas fa-calendar-check text-3xl text-blue-400"></i>
            </div>
        </div>
        <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Hadir Hari Ini</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ \App\Models\Presensi::whereDate('tanggal', today())->where('status', 1)->count() }}
                    </p>
                </div>
                <i class="fas fa-check-circle text-3xl text-green-400"></i>
            </div>
        </div>
        <div class="bg-purple-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Presensi Bulan Ini</p>
                    <p class="text-2xl font-bold text-purple-600">
                        {{ \App\Models\Presensi::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count() }}
                    </p>
                </div>
                <i class="fas fa-calendar-alt text-3xl text-purple-400"></i>
            </div>
        </div>
    </div>
</div>
@endsection

