<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\StockOpnameController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PresensiController;

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Role
Route::get('/pilih-role', [RoleController::class, 'index'])->name('pilih.role');
Route::post('/pilih-role', [RoleController::class, 'select'])->name('role.select');

// Presensi (Karyawan)
Route::get('/presensi', [PresensiController::class, 'form'])->name('presensi.form');
Route::post('/presensi', [PresensiController::class, 'store'])->name('presensi.store');

// Admin Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Panel (protected)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('medicines', MedicineController::class);

    Route::get('/cashier', [CashierController::class, 'index'])->name('cashier.index');
    Route::post('/cashier', [CashierController::class, 'store'])->name('cashier.store');
    Route::get('/cashier/receipt/{id}', [CashierController::class, 'receipt'])->name('cashier.receipt');
    Route::get('/cashier/history', [CashierController::class, 'history'])->name('cashier.history');
    
    // Stock Opname
    Route::resource('stock-opname', StockOpnameController::class);
    Route::post('/stock-opname/{stockOpname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opname.approve');
});