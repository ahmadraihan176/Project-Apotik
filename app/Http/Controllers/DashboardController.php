<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Dashboard admin - hanya untuk admin
        // Pastikan user adalah admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
        }

        $totalMedicines = Medicine::count();
        $totalStock = Medicine::sum('stock');
        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $todayRevenue = Transaction::whereDate('created_at', today())->sum('total_amount');
        
        $lowStock = Medicine::where('stock', '<=', 10)->get();
        $recentTransactions = Transaction::with('details.medicine', 'user')
            ->latest()
            ->take(5)
            ->get();

        // Admin dashboard selalu menggunakan layouts.admin
        return view('admin.dashboard', compact(
            'totalMedicines',
            'totalStock',
            'todayTransactions',
            'todayRevenue',
            'lowStock',
            'recentTransactions'
        ));
    }
}