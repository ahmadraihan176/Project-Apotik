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
use App\Http\Controllers\PenerimaanFarmasiController;
use App\Http\Controllers\JatuhTempoController;

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
    Route::get('/medicines/autocomplete', [MedicineController::class, 'autocomplete'])->name('medicines.autocomplete');

    Route::get('/cashier', [CashierController::class, 'index'])->name('cashier.index');
    Route::post('/cashier', [CashierController::class, 'store'])->name('cashier.store');
    Route::get('/cashier/receipt/{id}', [CashierController::class, 'receipt'])->name('cashier.receipt');
    Route::get('/cashier/history', [CashierController::class, 'history'])->name('cashier.history');
    
    // Penerimaan Farmasi
    Route::get('/penerimaan-farmasi', [PenerimaanFarmasiController::class, 'create'])->name('penerimaan-farmasi.create');
    Route::post('/penerimaan-farmasi', [PenerimaanFarmasiController::class, 'store'])->name('penerimaan-farmasi.store');
    
    // Jatuh Tempo
    Route::get('/jatuh-tempo', [JatuhTempoController::class, 'index'])->name('jatuh-tempo.index');
    Route::post('/jatuh-tempo/{id}/mark-paid', [JatuhTempoController::class, 'markAsPaid'])->name('jatuh-tempo.mark-paid');
    
    // Stock Opname
    // Route khusus harus diletakkan SEBELUM resource route untuk menghindari konflik
    Route::get('/stock-opname/get-medicine-batch', [StockOpnameController::class, 'getMedicineBatch'])->name('stock-opname.get-medicine-batch');
    Route::post('/stock-opname/{stockOpname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opname.approve');
    Route::resource('stock-opname', StockOpnameController::class);
});