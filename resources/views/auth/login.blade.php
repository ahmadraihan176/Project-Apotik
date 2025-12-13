@extends('layouts.app')

@section('title', 'Login Admin / Presensi Karyawan')

@section('content')
<div class="min-h-screen flex items-center justify-center gradient-bg py-12 px-4">
    <div class="max-w-md w-full">

        {{-- Card --}}
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-2xl p-8 border border-white/20">

            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-building-user text-4xl text-sky-600"></i>
                </div>
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
                        <input type="email" name="admin_email" id="adminEmail"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-sky-500"
                            placeholder="admin@langsefarma.com" value="{{ old('email') }}">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Password</label>
                        <input type="password" name="admin_password" id="adminPassword"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-sky-500"
                            placeholder="********">
                    </div>
                </div>

                {{-- FORM KARYAWAN --}}
                <div id="karyawanForm" class="hidden">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Email</label>
                        <input type="email" name="karyawan_email" id="karyawanEmail"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-sky-500"
                            placeholder="karyawan@langsefarma.com" value="{{ old('email') }}">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Password</label>
                        <input type="password" name="karyawan_password" id="karyawanPassword"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-sky-500"
                            placeholder="********">
                    </div>
                </div>
                
                {{-- Hidden inputs untuk email dan password yang akan diisi oleh JavaScript --}}
                <input type="hidden" name="email" id="emailField">
                <input type="hidden" name="password" id="passwordField">

                {{-- SUBMIT BUTTON --}}
                <button type="submit"
                    class="w-full gradient-bg text-white font-semibold py-3 rounded-lg shadow-lg hover:opacity-90 transition">
                    <i class="fas fa-check-circle mr-2"></i>Submit
                </button>
            </form>

            {{-- BACK BUTTON --}}
            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="text-white hover:text-gray-100 font-medium transition">
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
    const adminEmail = document.getElementById('adminEmail');
    const adminPassword = document.getElementById('adminPassword');
    const karyawanEmail = document.getElementById('karyawanEmail');
    const karyawanPassword = document.getElementById('karyawanPassword');

    adminForm.classList.add('hidden');
    karyawanForm.classList.add('hidden');

    if (this.value === 'admin') {
        adminForm.classList.remove('hidden');
        // Clear karyawan form
        if (karyawanEmail) karyawanEmail.value = '';
        if (karyawanPassword) karyawanPassword.value = '';
    } else if (this.value === 'karyawan') {
        karyawanForm.classList.remove('hidden');
        // Clear admin form
        if (adminEmail) adminEmail.value = '';
        if (adminPassword) adminPassword.value = '';
    }
});

// Validasi client-side sebelum submit dan copy value ke hidden fields
document.querySelector('form').addEventListener('submit', function(e) {
    const roleSelect = document.getElementById('roleSelect');
    const emailField = document.getElementById('emailField');
    const passwordField = document.getElementById('passwordField');
    
    if (!roleSelect.value) {
        e.preventDefault();
        alert('Silakan pilih role terlebih dahulu.');
        return false;
    }
    
    if (roleSelect.value === 'admin') {
        const adminEmail = document.getElementById('adminEmail');
        const adminPassword = document.getElementById('adminPassword');
        if (!adminEmail || !adminEmail.value) {
            e.preventDefault();
            alert('Email harus diisi.');
            return false;
        }
        if (!adminPassword || !adminPassword.value) {
            e.preventDefault();
            alert('Password harus diisi.');
            return false;
        }
        // Copy value ke hidden fields
        emailField.value = adminEmail.value;
        passwordField.value = adminPassword.value;
    } else if (roleSelect.value === 'karyawan') {
        const karyawanEmail = document.getElementById('karyawanEmail');
        const karyawanPassword = document.getElementById('karyawanPassword');
        if (!karyawanEmail || !karyawanEmail.value) {
            e.preventDefault();
            alert('Email harus diisi.');
            return false;
        }
        if (!karyawanPassword || !karyawanPassword.value) {
            e.preventDefault();
            alert('Password harus diisi.');
            return false;
        }
        // Copy value ke hidden fields
        emailField.value = karyawanEmail.value;
        passwordField.value = karyawanPassword.value;
    }
});
</script>

@endsection