<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\SuratMasukApiController;
use App\Http\Controllers\Api\V1\SuratKeluarApiController;
use App\Http\Controllers\Api\V1\DisposisiApiController;
use App\Http\Controllers\Api\V1\NotifikasiApiController;
use App\Http\Controllers\Api\V1\LaporanApiController;
use App\Http\Controllers\Api\V1\UserApiController;

// ── API v1 ─────────────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    // ── Auth (publik — tidak perlu token) ───────────────────────────────────
    Route::post('auth/login', [AuthController::class, 'login'])->name('api.auth.login');

    // ── Auth (perlu token) ─────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('auth/me',     [AuthController::class, 'me'])->name('api.auth.me');

        // ── Dashboard ───────────────────────────────────────────────────────
        Route::prefix('dashboard')->name('api.dashboard.')->group(function () {
            Route::get('stats',               [DashboardApiController::class, 'stats'])->name('stats');
            Route::get('surat-terbaru',       [DashboardApiController::class, 'suratTerbaru'])->name('surat-terbaru');
            Route::get('deadline-minggu-ini', [DashboardApiController::class, 'deadlineMingguIni'])->name('deadline');
        });

        // ── Surat Masuk ─────────────────────────────────────────────────────
        Route::prefix('surat-masuk')->name('api.surat-masuk.')->group(function () {
            Route::get('/',          [SuratMasukApiController::class, 'index'])->name('index');
            Route::post('/',         [SuratMasukApiController::class, 'store'])->name('store');
            Route::get('/{id}',      [SuratMasukApiController::class, 'show'])->name('show');
            Route::put('/{id}',      [SuratMasukApiController::class, 'update'])->name('update');
            Route::delete('/{id}',   [SuratMasukApiController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/baca', [SuratMasukApiController::class, 'tandaiBaca'])->name('baca');
        });

        // ── Surat Keluar ────────────────────────────────────────────────────
        Route::prefix('surat-keluar')->name('api.surat-keluar.')->group(function () {
            Route::get('/',        [SuratKeluarApiController::class, 'index'])->name('index');
            Route::post('/',       [SuratKeluarApiController::class, 'store'])->name('store');
            Route::get('/{id}',    [SuratKeluarApiController::class, 'show'])->name('show');
            Route::put('/{id}',    [SuratKeluarApiController::class, 'update'])->name('update');
            Route::delete('/{id}', [SuratKeluarApiController::class, 'destroy'])->name('destroy');
        });

        // ── Disposisi ───────────────────────────────────────────────────────
        Route::prefix('disposisi')->name('api.disposisi.')->group(function () {
            Route::get('/',                    [DisposisiApiController::class, 'index'])->name('index');
            Route::post('/',                   [DisposisiApiController::class, 'store'])->name('store');
            Route::get('/{id}',                [DisposisiApiController::class, 'show'])->name('show');
            Route::post('/{id}/teruskan',      [DisposisiApiController::class, 'teruskan'])->name('teruskan');
            Route::post('/{id}/laporan',       [DisposisiApiController::class, 'simpanLaporan'])->name('laporan');
            Route::post('/{id}/tanggapi',      [DisposisiApiController::class, 'tanggapi'])->name('tanggapi');
            Route::patch('/{id}/batal',        [DisposisiApiController::class, 'batalkan'])->name('batal');
        });

        // ── Notifikasi ──────────────────────────────────────────────────────
        Route::prefix('notifikasi')->name('api.notifikasi.')->group(function () {
            Route::get('/',                   [NotifikasiApiController::class, 'index'])->name('index');
            Route::get('/unread-count',       [NotifikasiApiController::class, 'unreadCount'])->name('unread-count');
            Route::patch('/{id}/read',        [NotifikasiApiController::class, 'markRead'])->name('read');
        });

        // ── Laporan ─────────────────────────────────────────────────────────
        Route::prefix('laporan')->name('api.laporan.')->group(function () {
            Route::get('surat-masuk',  [LaporanApiController::class, 'suratMasuk'])->name('surat-masuk');
            Route::get('surat-keluar', [LaporanApiController::class, 'suratKeluar'])->name('surat-keluar');
            Route::get('kinerja',      [LaporanApiController::class, 'kinerja'])->name('kinerja');
            Route::get('stats',        [LaporanApiController::class, 'stats'])->name('stats');
        });

        // ── Users & Unit Kerja (helpers untuk picker) ───────────────────────
        Route::get('users',      [UserApiController::class, 'index'])->name('api.users.index');
        Route::get('unit-kerja', [UserApiController::class, 'unitKerja'])->name('api.unit-kerja.index');

    }); // end auth:sanctum

}); // end v1
