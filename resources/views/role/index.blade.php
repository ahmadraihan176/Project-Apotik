@extends('layouts.app')

@section('title', 'Pilih Role')

@section('content')
<div class="min-h-screen flex items-center justify-center gradient-bg p-6">
    <div class="bg-white p-8 rounded-xl shadow-xl max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-circle text-4xl text-sky-600"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Pilih Role</h2>
            <p class="text-gray-500">Silakan pilih akses sesuai kebutuhan</p>
        </div>

        <div class="space-y-4">
            <a href="{{ route('login.form') }}" 
                class="flex items-center p-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md hover:shadow-lg">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user-shield text-2xl"></i>
                </div>
                <div class="text-left flex-1">
                    <div class="font-semibold text-lg">Admin</div>
                    <div class="text-sm opacity-90">Login untuk akses admin panel</div>
                </div>
                <i class="fas fa-chevron-right"></i>
            </a>

            <a href="{{ route('presensi.form') }}" 
                class="flex items-center p-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-md hover:shadow-lg">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-qrcode text-2xl"></i>
                </div>
                <div class="text-left flex-1">
                    <div class="font-semibold text-lg">Presensi Karyawan</div>
                    <div class="text-sm opacity-90">Scan barcode untuk presensi</div>
                </div>
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-800 text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection