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
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.dashboard') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-home mr-3"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.medicines.index') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.medicines.*') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-pills mr-3"></i>
                    <span>Data Obat</span>
                </a>
                <a href="{{ route('admin.cashier.index') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.cashier.index') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-cash-register mr-3"></i>
                    <span>Kasir</span>
                </a>
                <a href="{{ route('admin.cashier.history') }}" class="flex items-center px-6 py-3 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('admin.cashier.history') ? 'bg-white bg-opacity-20' : '' }}">
                    <i class="fas fa-history mr-3"></i>
                    <span>Riwayat Transaksi</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-8 py-4">
                    <h1 class="text-2xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST">
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