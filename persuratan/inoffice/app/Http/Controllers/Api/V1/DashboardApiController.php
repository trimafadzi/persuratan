<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuratMasukResource;
use App\Http\Resources\DisposisiResource;
use App\Models\SuratMasuk;
use App\Models\Disposisi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardApiController extends Controller
{
    /**
     * GET /api/v1/dashboard/stats
     * Statistik ringkasan untuk card dashboard mobile.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'data' => [
                'surat_belum_dibaca'  => SuratMasuk::where('status', 'belum_dibaca')->count(),
                'disposisi_pending'   => $this->getDisposisiPendingCount($user),
                'deadline_hari_ini'   => $this->getDeadlineHariIni($user),
                'surat_selesai_bulan' => SuratMasuk::where('status', 'selesai')
                                            ->whereMonth('updated_at', now()->month)
                                            ->whereYear('updated_at', now()->year)
                                            ->count(),
            ],
        ]);
    }

    /**
     * GET /api/v1/dashboard/surat-terbaru
     * 5 surat masuk terbaru untuk list preview di dashboard.
     */
    public function suratTerbaru(): JsonResponse
    {
        $suratTerbaru = SuratMasuk::with(['creator', 'unitKerja'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => SuratMasukResource::collection($suratTerbaru),
        ]);
    }

    /**
     * GET /api/v1/dashboard/deadline-minggu-ini
     * Disposisi yang deadline-nya minggu ini (belum selesai/dibatalkan).
     */
    public function deadlineMingguIni(): JsonResponse
    {
        $user = Auth::user();

        $deadline = Disposisi::with(['suratMasuk', 'pemberi'])
            ->whereNotNull('tanggal_deadline')
            ->whereBetween('tanggal_deadline', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotIn('status', ['selesai', 'dibatalkan'])
            ->whereHas('penerima', fn($q) => $q->where('users.id', $user->id))
            ->orderBy('tanggal_deadline')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => DisposisiResource::collection($deadline),
        ]);
    }

    // ── Private helpers (sama logikanya dengan DashboardController) ──────────

    private function getDisposisiPendingCount($user): int
    {
        return Disposisi::where('status', 'pending')
            ->whereHas('penerima', fn($q) => $q->where('users.id', $user->id))
            ->count();
    }

    private function getDeadlineHariIni($user): int
    {
        return Disposisi::whereDate('tanggal_deadline', today())
            ->whereNotIn('status', ['selesai', 'dibatalkan'])
            ->whereHas('penerima', fn($q) => $q->where('users.id', $user->id))
            ->count();
    }
}
