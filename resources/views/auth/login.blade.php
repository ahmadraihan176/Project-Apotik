@extends('layouts.app')

@section('title', 'Login Admin / Presensi Karyawan')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-sky-500 to-blue-700 py-12 px-4">
    <div class="max-w-md w-full">

        {{-- Card --}}
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-2xl p-8 border border-white/20">

            {{-- Header --}}
            <div class="text-center mb-6">
                <i class="fas fa-building-user text-5xl text-sky-600 mb-3"></i>
                <h2 class="text-3xl font-extrabold text-gray-800">Langse Farma</h2>
                <p class="text-gray-600 mt-1">Silakan pilih role terlebih dahulu</p>
            </div>

            {{-- NOTIFIKASI --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- FORM --}}
            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf

                {{-- ROLE SELECT --}}
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Pilih Role</label>
                    <select name="role" id="roleSelect"
                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-sky-500">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="karyawan">Karyawan</option>
                    </select>
                </div>

                {{-- FORM ADMIN --}}
                <div id="adminForm" class="hidden">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Email</label>
                        <input type="email" name="email"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-sky-500"
                            placeholder="admin@langsefarma.com">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Password</label>
                        <input type="password" name="password"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-sky-500"
                            placeholder="********">
                    </div>
                </div>

                {{-- FORM PRESENSI KARYAWAN --}}
                <div id="karyawanForm" class="hidden">
                    <p class="text-gray-700 font-semibold mb-2">Pilih Nama Karyawan:</p>

                    <div class="space-y-3">
                        @php
                            $karyawanList = ['Firza', 'Budi', 'Agus', 'Siti', 'Rian'];
                        @endphp

                        @foreach($karyawanList as $nama)
                            <label class="flex items-center space-x-3 bg-sky-50 px-4 py-3 rounded-lg border cursor-pointer hover:bg-sky-100 transition">
                                <input type="radio" name="nama" value="{{ $nama }}" class="h-5 w-5 text-sky-600">
                                <span class="text-gray-800 font-medium">{{ $nama }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- SUBMIT BUTTON --}}
                <button type="submit"
                    class="w-full bg-sky-600 text-white font-semibold py-3 rounded-lg shadow-md hover:bg-sky-700 transition">
                    <i class="fas fa-check-circle mr-2"></i>Submit
                </button>
            </form>

            {{-- BACK BUTTON --}}
            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="text-sky-200 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
                </a>
            </div>

        </div>
    </div>
</div>

{{-- SCRIPT ROLE SWITCH --}}
<script>
document.getElementById('roleSelect').addEventListener('change', function () {
    const adminForm = document.getElementById('adminForm');
    const karyawanForm = document.getElementById('karyawanForm');

    adminForm.classList.add('hidden');
    karyawanForm.classList.add('hidden');

    if (this.value === 'admin') {
        adminForm.classList.remove('hidden');
    } else if (this.value === 'karyawan') {
        karyawanForm.classList.remove('hidden');
    }
});
</script>

@endsection