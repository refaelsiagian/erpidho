<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalPengirimanController;
use App\Http\Controllers\KaryawanTukangController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PengadaanController;
use App\Http\Controllers\PenggajianTukangController;
use App\Http\Controllers\PesananBoronganController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransaksiPenjualanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])
        ->middleware('throttle:login')
        ->name('login.authenticate');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Material index is readable by Pemilik & Keluarga; mutations are Pemilik-only below
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');

    Route::resource('pelanggans', PelangganController::class)->except(['show']);

    // FR-02: Penjualan Langsung & Pesanan Borongan
    Route::prefix('penjualan')->name('penjualan.')->group(function (): void {
        Route::get('/', [TransaksiPenjualanController::class, 'index'])->name('index');
        Route::get('/create', [TransaksiPenjualanController::class, 'create'])->name('create');
        Route::post('/', [TransaksiPenjualanController::class, 'store'])->name('store');
        Route::get('/{penjualan}', [TransaksiPenjualanController::class, 'show'])->name('show');
        Route::patch('/{penjualan}/status', [TransaksiPenjualanController::class, 'updateStatus'])->name('status');
        Route::get('/{penjualan}/pdf', [TransaksiPenjualanController::class, 'pdf'])->name('pdf');
    });

    Route::prefix('borongan')->name('borongan.')->group(function (): void {
        Route::get('/', [PesananBoronganController::class, 'index'])->name('index');
        Route::get('/create', [PesananBoronganController::class, 'create'])->name('create');
        Route::post('/', [PesananBoronganController::class, 'store'])->name('store');
        Route::get('/{borongan}', [PesananBoronganController::class, 'show'])->name('show');
        Route::patch('/{borongan}/status', [PesananBoronganController::class, 'updateStatus'])->name('status');
        Route::patch('/{borongan}/payment', [PesananBoronganController::class, 'updatePaymentStatus'])->name('payment');
        Route::get('/{borongan}/pdf', [PesananBoronganController::class, 'pdf'])->name('pdf');
    });

    // FR-03: Jadwal Pengiriman
    Route::prefix('pengiriman')->name('pengiriman.')->group(function (): void {
        Route::get('/', [JadwalPengirimanController::class, 'index'])->name('index');
        Route::get('/create', [JadwalPengirimanController::class, 'create'])->name('create');
        Route::post('/', [JadwalPengirimanController::class, 'store'])->name('store');
        Route::patch('/{pengiriman}/status', [JadwalPengirimanController::class, 'updateStatus'])->name('status');
    });

    // FR-05: Pengadaan & Pemantauan Stok
    Route::prefix('pengadaan')->name('pengadaan.')->group(function (): void {
        Route::get('/', [PengadaanController::class, 'index'])->name('index');
        Route::get('/create', [PengadaanController::class, 'create'])->name('create');
        Route::post('/', [PengadaanController::class, 'store'])->name('store');
        Route::get('/{pengadaan}', [PengadaanController::class, 'show'])->name('show');
        Route::post('/{pengadaan}/receive', [PengadaanController::class, 'receive'])->name('receive');
        Route::get('/{pengadaan}/pdf', [PengadaanController::class, 'pdf'])->name('pdf');
    });

    // Pemilik-only routes: Master data sensitif, Verifikasi & Penggajian Tukang, Laporan
    Route::middleware('role:Pemilik')->group(function (): void {
        Route::resource('materials', MaterialController::class)->except(['index', 'show']);
        Route::resource('tukangs', KaryawanTukangController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class)->except(['show']);
        Route::resource('pengguna', UserController::class)->except(['show']);

        // FR-04: Verifikasi Proyek & Penggajian Tukang
        Route::prefix('penggajian')->name('penggajian.')->group(function (): void {
            Route::get('/', [PenggajianTukangController::class, 'index'])->name('index');
            Route::post('/', [PenggajianTukangController::class, 'store'])->name('store');
            Route::patch('/{penggajian}/status', [PenggajianTukangController::class, 'updateStatus'])->name('status');
            Route::get('/{penggajian}/pdf', [PenggajianTukangController::class, 'pdf'])->name('pdf');
        });

        // FR-06: Pelaporan Operasional
        Route::prefix('laporan')->name('laporan.')->group(function (): void {
            Route::get('/', [LaporanController::class, 'index'])->name('index');
            Route::get('/pdf', [LaporanController::class, 'pdf'])->name('pdf');
        });
    });
});
