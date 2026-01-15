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
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KaryawanDashboardController;
use App\Http\Controllers\ReportController;

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Role
Route::get('/pilih-role', [RoleController::class, 'index'])->name('pilih.role');

// Presensi (Karyawan)
Route::get('/presensi', [PresensiController::class, 'form'])->name('presensi.form');
Route::post('/presensi', [PresensiController::class, 'store'])->name('presensi.store');

// Dashboard Karyawan (protected dengan auth) - bisa akses menu yang sama dengan admin
Route::middleware(['auth'])->prefix('karyawan')->name('karyawan.')->group(function () {
    Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [KaryawanDashboardController::class, 'logout'])->name('logout');

    // Menu yang sama dengan admin (kecuali Master Karyawan dan Presensi)
    Route::resource('medicines', MedicineController::class)->except(['create', 'store', 'edit', 'update']);
    Route::get('/cashier', [CashierController::class, 'index'])->name('cashier.index');
    Route::post('/cashier', [CashierController::class, 'store'])->name('cashier.store');
    Route::get('/cashier/receipt/{id}', [CashierController::class, 'receipt'])->name('cashier.receipt');
    Route::get('/cashier/history', [CashierController::class, 'history'])->name('cashier.history');
    
    // Penerimaan Farmasi
    Route::get('/penerimaan-farmasi', [PenerimaanFarmasiController::class, 'create'])->name('penerimaan-farmasi.create');
    Route::get('/penerimaan-farmasi/get-no-urut', [PenerimaanFarmasiController::class, 'getNoUrut'])->name('penerimaan-farmasi.get-no-urut');
    Route::post('/penerimaan-farmasi', [PenerimaanFarmasiController::class, 'store'])->name('penerimaan-farmasi.store');
    
    // Jatuh Tempo
    Route::get('/jatuh-tempo', [JatuhTempoController::class, 'index'])->name('jatuh-tempo.index');
    Route::post('/jatuh-tempo/{id}/mark-paid', [JatuhTempoController::class, 'markAsPaid'])->name('jatuh-tempo.mark-paid');
    
    // Stock Opname
    Route::get('/stock-opname/get-medicine-batch', [StockOpnameController::class, 'getMedicineBatch'])->name('stock-opname.get-medicine-batch');
    Route::post('/stock-opname/{stockOpname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opname.approve');
    Route::resource('stock-opname', StockOpnameController::class);
});

// Admin Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login-form', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Panel (protected)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('medicines', MedicineController::class)->except(['create', 'store', 'edit', 'update']);
    Route::get('/medicines/autocomplete', [MedicineController::class, 'autocomplete'])->name('medicines.autocomplete');
    Route::post('/medicines/{medicine}/update-price', [MedicineController::class, 'updatePrice'])->name('medicines.update-price');

    Route::get('/cashier', [CashierController::class, 'index'])->name('cashier.index');
    Route::post('/cashier', [CashierController::class, 'store'])->name('cashier.store');
    Route::get('/cashier/receipt/{id}', [CashierController::class, 'receipt'])->name('cashier.receipt');
    Route::get('/cashier/history', [CashierController::class, 'history'])->name('cashier.history');
    
    // Penerimaan Farmasi
    Route::get('/penerimaan-farmasi', [PenerimaanFarmasiController::class, 'create'])->name('penerimaan-farmasi.create');
    Route::get('/penerimaan-farmasi/get-no-urut', [PenerimaanFarmasiController::class, 'getNoUrut'])->name('penerimaan-farmasi.get-no-urut');
    Route::post('/penerimaan-farmasi', [PenerimaanFarmasiController::class, 'store'])->name('penerimaan-farmasi.store');
    
    // Jatuh Tempo
    Route::get('/jatuh-tempo', [JatuhTempoController::class, 'index'])->name('jatuh-tempo.index');
    Route::post('/jatuh-tempo/{id}/mark-paid', [JatuhTempoController::class, 'markAsPaid'])->name('jatuh-tempo.mark-paid');
    
    // Stock Opname
    // Route khusus harus diletakkan SEBELUM resource route untuk menghindari konflik
    Route::get('/stock-opname/get-medicine-batch', [StockOpnameController::class, 'getMedicineBatch'])->name('stock-opname.get-medicine-batch');
    Route::post('/stock-opname/{stockOpname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opname.approve');
    Route::resource('stock-opname', StockOpnameController::class);

    // Master Karyawan
    Route::resource('karyawan', KaryawanController::class)->except(['show']);

    // Manajemen Presensi (Hanya Admin)
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::get('/presensi/rekapan', [PresensiController::class, 'rekapan'])->name('presensi.rekapan');

    // Laporan Bulanan
    Route::get('/report/monthly', [ReportController::class, 'monthlyReport'])->name('report.monthly');
    Route::get('/report/monthly/print', [ReportController::class, 'printMonthlyReport'])->name('report.monthly.print');
    
    // Rekapan Pembelian Obat
    Route::get('/report/rekapan-pembelian-obat', [ReportController::class, 'rekapanPembelianObat'])->name('report.rekapan-pembelian-obat');
    
    // Laporan Laba Rugi
    Route::get('/report/laba-rugi', [ReportController::class, 'labaRugi'])->name('report.laba-rugi');
    Route::get('/report/laba-rugi/print', [ReportController::class, 'printLabaRugi'])->name('report.laba-rugi.print');

});