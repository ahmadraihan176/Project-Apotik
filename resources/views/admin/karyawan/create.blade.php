@extends('layouts.admin')

@section('title', 'Tambah Karyawan')
@section('header', 'Tambah Karyawan Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.karyawan.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Nama Karyawan *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 @error('name') border-red-500 @enderror"
                    placeholder="Masukkan nama karyawan">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">NIK (Nomor Induk Karyawan) *</label>
                <input type="text" name="nik" value="{{ old('nik') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 @error('nik') border-red-500 @enderror"
                    placeholder="Masukkan NIK dari ID card">
                <p class="text-xs text-gray-500 mt-1">Masukkan NIK yang ada di ID card karyawan</p>
                @error('nik')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Email (Opsional)</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 @error('email') border-red-500 @enderror"
                    placeholder="email@example.com">
                <p class="text-xs text-gray-500 mt-1">Jika tidak diisi, akan di-generate otomatis</p>
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="flex-1 gradient-bg text-white font-semibold py-2 rounded-lg hover:opacity-90">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
                <a href="{{ route('admin.karyawan.index') }}" class="flex-1 text-center bg-gray-300 text-gray-700 font-semibold py-2 rounded-lg hover:bg-gray-400">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection


