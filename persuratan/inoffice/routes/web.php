<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UnitKerjaController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\LogController;

// ── Auth ────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Authenticated Routes ────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Surat Masuk
    Route::resource('surat-masuk', SuratMasukController::class);
    Route::patch('surat-masuk/{suratMasuk}/baca', [SuratMasukController::class, 'tandaiBaca'])
         ->name('surat-masuk.baca');

    // Surat Keluar
    Route::resource('surat-keluar', SuratKeluarController::class);

    // Disposisi
    Route::resource('disposisi', DisposisiController::class);
    Route::post('disposisi/{disposisi}/teruskan', [DisposisiController::class, 'teruskan'])
         ->name('disposisi.teruskan');
    Route::post('disposisi/{disposisi}/laporan', [DisposisiController::class, 'simpanLaporan'])
         ->name('disposisi.laporan');
    Route::post('disposisi/{disposisi}/tanggapi', [DisposisiController::class, 'tanggapi'])
         ->name('disposisi.tanggapi');
    Route::patch('disposisi/{disposisi}/batal', [DisposisiController::class, 'batalkan'])
         ->name('disposisi.batal');

    // Draft Surat
    Route::resource('draft', DraftController::class);
    Route::post('draft/{draft}/submit-review', [DraftController::class, 'submitReview'])
         ->name('draft.submit-review');
    Route::post('draft/{draft}/approve', [DraftController::class, 'approve'])
         ->name('draft.approve');
    Route::post('draft/{draft}/revisi', [DraftController::class, 'revisi'])
         ->name('draft.revisi');

    // Laporan
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/surat-masuk', [LaporanController::class, 'suratMasuk'])->name('laporan.surat-masuk');
    Route::get('laporan/surat-keluar', [LaporanController::class, 'suratKeluar'])->name('laporan.surat-keluar');
    Route::get('laporan/kinerja', [LaporanController::class, 'kinerja'])->name('laporan.kinerja');
    Route::get('laporan/export/{type}/{format}', [LaporanController::class, 'export'])->name('laporan.export');

    Route::get('notifikasi/{notifikasi}/read', function (\App\Models\Notifikasi $notifikasi) {
        if ($notifikasi->user_id !== auth()->id()) {
            abort(403);
        }
        $notifikasi->markAsRead();
        
        if ($notifikasi->tipe === 'disposisi' || $notifikasi->tipe === 'laporan') {
            return redirect()->route('disposisi.index');
        } elseif ($notifikasi->tipe === 'surat_masuk') {
            return redirect()->route('surat-masuk.index');
        }
        return redirect()->back();
    })->name('notifikasi.read');

    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('unit-kerja', UnitKerjaController::class);
        Route::resource('roles', RoleController::class);
        Route::get('log', [LogController::class, 'index'])->name('log.index');
    });

});
