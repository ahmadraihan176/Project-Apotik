@extends('layouts.app')

@section('title', 'Beranda - Langse Farma')

@section('content')
<!-- Navigation -->
<nav class="bg-white shadow-md">
    <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fas fa-heartbeat text-3xl text-sky-500"></i>
                <h1 class="text-2xl font-bold text-gray-800">Langse Farma</h1>
            </div>
            <a href="{{ route('login') }}" class="px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                <i class="fas fa-sign-in-alt mr-2"></i>Login Admin
            </a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
 <!-- Hero Section -->
<section class="gradient-bg text-white py-20">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-5xl font-bold mb-4">Selamat Datang di Langse Farma</h2>
        <p class="text-xl mb-8 opacity-90">Apotek Terpercaya untuk Kesehatan Keluarga Anda</p>
        <div class="flex justify-center space-x-4">
            <div class="bg-white text-sky-600 px-6 py-3 rounded-lg shadow-lg">
                <i class="fas fa-pills text-2xl mb-2"></i>
                <p class="font-semibold">Obat Lengkap</p>
            </div>
            <div class="bg-white text-sky-600 px-6 py-3 rounded-lg shadow-lg">
                <i class="fas fa-user-md text-2xl mb-2"></i>
                <p class="font-semibold">Pelayanan Profesional</p>
            </div>
            <div class="bg-white text-sky-600 px-6 py-3 rounded-lg shadow-lg">
                <i class="fas fa-clock text-2xl mb-2"></i>
                <p class="font-semibold">Buka 07.00 - 20.00</p>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <h3 class="text-3xl font-bold text-gray-800 mb-4">Tentang Kami</h3>
            <div class="w-20 h-1 bg-sky-500 mx-auto"></div>
        </div>
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <img src="apotik.jpg" alt="Apotek" class="rounded-lg shadow-lg">
            </div>
            <div>
                <h4 class="text-2xl font-semibold text-gray-800 mb-4">Langse Farma - Partner Kesehatan Anda</h4>
                <p class="text-gray-600 mb-4">
                    Langse Farma adalah apotek modern yang berkomitmen untuk menyediakan produk kesehatan berkualitas tinggi dengan harga terjangkau. Kami melayani dengan sepenuh hati untuk kesehatan dan kesejahteraan Anda.
                </p>
                <ul class="space-y-2 text-gray-600">
                    <li><i class="fas fa-check-circle text-sky-500 mr-2"></i>Obat-obatan resmi dan terjamin</li>
                    <li><i class="fas fa-check-circle text-sky-500 mr-2"></i>Farmasis berpengalaman</li>
                    <li><i class="fas fa-check-circle text-sky-500 mr-2"></i>Konsultasi gratis</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <h3 class="text-3xl font-bold text-gray-800 mb-4">Layanan Kami</h3>
            <div class="w-20 h-1 bg-sky-500 mx-auto"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition text-center">
                <div class="w-16 h-16 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-prescription-bottle-alt text-3xl text-sky-500"></i>
                </div>
                <h5 class="text-xl font-semibold mb-2">Penjualan Obat</h5>
                <p class="text-gray-600">Menyediakan berbagai macam obat dengan atau tanpa resep dokter</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition text-center">
                <div class="w-16 h-16 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-stethoscope text-3xl text-sky-500"></i>
                </div>
                <h5 class="text-xl font-semibold mb-2">Konsultasi Farmasi</h5>
                <p class="text-gray-600">Konsultasi gratis dengan apoteker profesional kami</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition text-center">
                <div class="w-16 h-16 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-truck text-3xl text-sky-500"></i>
                </div>
                <h5 class="text-xl font-semibold mb-2">Delivery Service</h5>
                <p class="text-gray-600">Layanan antar obat ke rumah untuk kenyamanan Anda</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-16 gradient-bg text-white">
    <div class="container mx-auto px-6 text-center">
        <h3 class="text-3xl font-bold mb-4">Hubungi Kami</h3>
        <p class="mb-8 text-lg">Kami siap melayani anda setiap hari</p>
        <div class="grid md:grid-cols-3 gap-8">
            <div>
                <i class="fas fa-phone text-3xl mb-2"></i>
                <p class="font-semibold">Telepon</p>
                <p>0274-123456</p>
            </div>
            <div>
                <i class="fas fa-envelope text-3xl mb-2"></i>
                <p class="font-semibold">Email</p>
                <p>info@langsefarma.com</p>
            </div>
            <div>
                <i class="fas fa-map-marker-alt text-3xl mb-2"></i>
                <p class="font-semibold">Alamat</p>
                <p>Jl. Randusari - Klaten, Nepen, Kec. Teras, Kabupaten Boyolali, Jawa Tengah</p>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-6">
    <div class="container mx-auto px-6 text-center">
        <p>&copy; 2024 Langse Farma. All rights reserved.</p>
    </div>
</footer>
@endsection