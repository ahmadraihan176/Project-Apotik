@extends('layouts.app')

@section('title', 'Presensi Karyawan')

@section('content')
<div class="min-h-screen flex items-center justify-center gradient-bg p-6">

    <div class="bg-white shadow-2xl rounded-3xl p-8 w-full max-w-lg">
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-user-check text-4xl text-sky-600"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Presensi Karyawan</h2>
            <p class="text-gray-500 mt-1">Silakan pilih nama dan lakukan presensi</p>
        </div>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-5">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('presensi.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block font-semibold text-gray-700 mb-3 text-lg">Pilih Nama Karyawan</label>

                <div class="space-y-3">

                    <!-- CHECKBOX HANYA 1 YANG BISA DIPILIH -->
                    @php
                        $karyawan = ['Firza', 'Budi', 'Siti', 'Dewi'];
                    @endphp

                    @foreach($karyawan as $nama)
                        <label class="flex items-center p-3 border-2 border-gray-200 rounded-xl hover:border-sky-400 hover:bg-sky-50 cursor-pointer transition">

                            <input type="checkbox"
                                   name="nama"
                                   value="{{ $nama }}"
                                   class="nama-checkbox w-5 h-5 text-sky-600 rounded mr-3 focus:ring-sky-500" />

                            <span class="text-gray-700 font-medium">{{ $nama }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                class="w-full py-3 rounded-xl gradient-bg text-white font-semibold text-lg shadow-lg hover:opacity-90 transition">
                <i class="fas fa-check-circle mr-2"></i> Submit Presensi
            </button>

        </form>

        <div class="text-center mt-6">
            <a href="{{ route('login') }}" class="text-sky-600 hover:text-sky-800 font-medium transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Halaman Login
            </a>
        </div>

    </div>
</div>

{{-- Script agar hanya 1 checkbox bisa dipilih --}}
<script>
    const checkboxes = document.querySelectorAll('.nama-checkbox');

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            checkboxes.forEach(other => {
                if (other !== cb) other.checked = false;
            });
        });
    });
</script>

@endsection