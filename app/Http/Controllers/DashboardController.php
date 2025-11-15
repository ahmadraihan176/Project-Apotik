<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalMedicines = Medicine::count();
        $totalStock = Medicine::sum('stock');
        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $todayRevenue = Transaction::whereDate('created_at', today())->sum('total_amount');
        
        $lowStock = Medicine::where('stock', '<=', 10)->get();
        $recentTransactions = Transaction::with('details.medicine', 'user')
            ->latest()
            ->take(5)
            ->get();

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