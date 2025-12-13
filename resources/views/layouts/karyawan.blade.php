<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Karyawan') - Langse Farma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #87CEEB 0%, #4682B4 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="flex h-screen">
        <aside class="w-64 gradient-bg text-white">
            <div class="p-6">
                <h2 class="text-2xl font-bold">Langse Farma</h2>
                <p class="text-sm opacity-80">Karyawan Panel</p>
            </div>
            <nav class="mt-6">
                <a href="{{ route('karyawan.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('karyawan.dashboard') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-home mr-3"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('karyawan.medicines.index') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('karyawan.medicines.*') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-pills mr-3"></i>
                    <span>Inventory</span>
                </a>
                <a href="{{ route('karyawan.stock-opname.index') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('karyawan.stock-opname.*') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-clipboard-check mr-3"></i>
                    <span>Stok Opname</span>
                </a>
                <a href="{{ route('karyawan.cashier.index') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('karyawan.cashier.index') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-cart-shopping mr-3"></i>
                    <span>Penjualan</span>
                </a>
                <a href="{{ route('karyawan.cashier.history') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('karyawan.cashier.history') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-history mr-3"></i>
                    <span>Riwayat Transaksi</span>
                </a>
                {{-- Master Karyawan TIDAK ada di menu karyawan --}}
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-8 py-4">
                    <h1 class="text-2xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h1>
                    <div class="flex items-center space-x-4">
                        @php
                            // Tampilkan nama karyawan yang login, atau "Langse Farma" jika tidak ada
                            $karyawan = auth()->check() && auth()->user() ? auth()->user()->name : 'Langse Farma';
                        @endphp
                        <span class="text-gray-600">{{ $karyawan }}</span>
                        <form action="{{ route('karyawan.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-8">
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

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
