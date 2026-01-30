<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        // Validasi: Pastikan user yang login adalah karyawan
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();
        
        // Pastikan role adalah karyawan
        if ($user->role !== 'karyawan') {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Akses ditolak. Hanya karyawan yang dapat mengakses halaman ini.');
        }

        // Karyawan menggunakan dashboard umum yang sama dengan admin
        // Gunakan logika yang sama dengan DashboardController
        $totalMedicines = \App\Models\Medicine::count();
        $totalStock = \App\Models\Medicine::sum('stock');
        $lowStock = \App\Models\Medicine::where('stock', '<=', 10)->count();
        
        // Statistik tidak ditampilkan untuk karyawan untuk performa dan privasi
        $todayTransactions = 0;
        $todayRevenue = 0;
        $todayProfit = 0;
        
        $monthTransactions = 0;
        $monthRevenue = 0;
        $monthProfit = 0;
        
        $totalTransactions = \App\Models\Transaction::count();
        $totalRevenue = \App\Models\Transaction::sum('total_amount');
        $lowStockMedicines = \App\Models\Medicine::where('stock', '<=', 10)->get();
        $recentTransactions = \App\Models\Transaction::with('details.medicine', 'user')
            ->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalMedicines', 'totalStock', 'lowStock', 'todayTransactions', 'todayRevenue', 'todayProfit',
            'monthTransactions', 'monthRevenue', 'monthProfit', 'totalTransactions', 'totalRevenue',
            'lowStockMedicines', 'recentTransactions'
        ));
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->with('success', 'Anda telah logout.');
    }
}
