<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Langse Farma</title>
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
                <p class="text-sm opacity-80">Admin Panel</p>
            </div>
            <nav class="mt-6">
                @php
                    $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
                @endphp
                <a href="{{ route($routePrefix . '.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.dashboard') || request()->routeIs('karyawan.dashboard') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-home mr-3"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route($routePrefix . '.medicines.index') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.medicines.*') || request()->routeIs('karyawan.medicines.*') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-pills mr-3"></i>
                    <span>Inventory</span>
                </a>
                <a href="{{ route($routePrefix . '.penerimaan-farmasi.create') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.penerimaan-farmasi.*') || request()->routeIs('karyawan.penerimaan-farmasi.*') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-box mr-3"></i>
                    <span>Penerimaan Farmasi</span>
                </a>
                <a href="{{ route($routePrefix . '.jatuh-tempo.index') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.jatuh-tempo.*') || request()->routeIs('karyawan.jatuh-tempo.*') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-calendar-times mr-3"></i>
                    <span>Jatuh Tempo</span>
                </a>
                <a href="{{ route($routePrefix . '.stock-opname.index') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.stock-opname.*') || request()->routeIs('karyawan.stock-opname.*') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-clipboard-check mr-3"></i>
                    <span>Stok Opname</span>
                </a>
                <a href="{{ route($routePrefix . '.cashier.index') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.cashier.index') || request()->routeIs('karyawan.cashier.index') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-cart-shopping mr-3"></i>
                    <span>Penjualan</span>
                </a>
                <a href="{{ route($routePrefix . '.cashier.history') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.cashier.history') || request()->routeIs('karyawan.cashier.history') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-history mr-3"></i>
                    <span>Riwayat Transaksi</span>
                </a>
                @if(auth()->check() && auth()->user()->role === 'admin')
                <a href="{{ route('admin.report.monthly') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.report.monthly') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-chart-bar mr-3"></i>
                    <span>Laporan Bulanan</span>
                </a>
                <a href="{{ route('admin.report.rekapan-pembelian-obat') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.report.rekapan-pembelian-obat') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-shopping-cart mr-3"></i>
                    <span>Rekapan Pembelian Obat</span>
                </a>
                <a href="{{ route('admin.report.laba-rugi') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.report.laba-rugi') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-chart-line mr-3"></i>
                    <span>Laporan Laba Rugi</span>
                </a>
                <a href="{{ route('admin.karyawan.index') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.karyawan.*') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-users mr-3"></i>
                    <span>Master Karyawan</span>
                </a>
                <a href="{{ route('admin.presensi.index') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.presensi.*') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-calendar-check mr-3"></i>
                    <span>Presensi Karyawan</span>
                </a>
                @endif
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
                            $userName = auth()->check() && auth()->user() ? auth()->user()->name : 'Admin';
                        @endphp
                        <span class="text-gray-600">{{ $userName }}</span>
                        @php
                            $logoutRoute = request()->routeIs('karyawan.*') ? route('karyawan.logout') : route('logout');
                        @endphp
                        <form action="{{ $logoutRoute }}" method="POST">
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