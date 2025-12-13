<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Karyawan bisa akses dashboard yang sama dengan admin
        // Ambil data dashboard
        $totalMedicines = \App\Models\Medicine::count();
        $totalStock = \App\Models\Medicine::sum('stock');
        $todayTransactions = \App\Models\Transaction::whereDate('created_at', today())->count();
        $todayRevenue = \App\Models\Transaction::whereDate('created_at', today())->sum('total_amount');
        
        $lowStock = \App\Models\Medicine::where('stock', '<=', 10)->get();
        $recentTransactions = \App\Models\Transaction::with('details.medicine', 'user')
            ->latest()
            ->take(5)
            ->get();

        // Gunakan view dashboard khusus karyawan yang langsung extends layouts.karyawan
        return view('karyawan.dashboard', compact(
            'totalMedicines',
            'totalStock',
            'todayTransactions',
            'todayRevenue',
            'lowStock',
            'recentTransactions'
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
