<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LaporanApiController extends Controller
{
    /**
     * GET /api/v1/laporan/stats
     * Statistik agregat untuk dashboard laporan mobile (read-only).
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_masuk'     => SuratMasuk::count(),
            'total_keluar'    => SuratKeluar::count(),
            'total_disposisi' => Disposisi::count(),
            'selesai_bulan'   => SuratMasuk::where('status', 'selesai')
                                    ->whereMonth('updated_at', now()->month)
                                    ->whereYear('updated_at', now()->year)
                                    ->count(),
        ];

        // Volume surat per bulan (6 bulan terakhir — cocok untuk chart mobile)
        $volumePerBulan = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $volumePerBulan[] = [
                'bulan'  => $date->format('M Y'),
                'masuk'  => SuratMasuk::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'keluar' => SuratKeluar::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ];
        }

        $statusBreakdown = SuratMasuk::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'data' => [
                'stats'           => $stats,
                'volume_per_bulan'=> $volumePerBulan,
                'status_breakdown'=> $statusBreakdown,
            ],
        ]);
    }

    /**
     * GET /api/v1/laporan/surat-masuk
     * Rekap surat masuk dalam rentang tanggal (paginated, read-only).
     */
    public function suratMasuk(Request $request): JsonResponse
    {
        $query = SuratMasuk::with(['creator', 'unitKerja'])->orderByDesc('tanggal_terima');

        if ($request->filled('dari'))   $query->whereDate('tanggal_terima', '>=', $request->dari);
        if ($request->filled('sampai')) $query->whereDate('tanggal_terima', '<=', $request->sampai);
        if ($request->filled('status')) $query->where('status', $request->status);

        $data = $query->paginate(50)->withQueryString();

        return response()->json([
            'data' => $data->items()
                ? array_map(fn($s) => [
                    'id'             => $s->id,
                    'nomor_surat'    => $s->nomor_surat,
                    'pengirim'       => $s->pengirim,
                    'perihal'        => $s->perihal,
                    'tanggal_terima' => $s->tanggal_terima?->format('Y-m-d'),
                    'status'         => $s->status,
                    'sifat'          => $s->sifat,
                ], $data->items())
                : [],
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/laporan/surat-keluar
     * Rekap surat keluar (paginated, read-only).
     */
    public function suratKeluar(Request $request): JsonResponse
    {
        $query = SuratKeluar::with(['creator'])->orderByDesc('tanggal');

        if ($request->filled('dari'))   $query->whereDate('tanggal', '>=', $request->dari);
        if ($request->filled('sampai')) $query->whereDate('tanggal', '<=', $request->sampai);

        $data = $query->paginate(50)->withQueryString();

        return response()->json([
            'data' => $data->items()
                ? array_map(fn($s) => [
                    'id'                   => $s->id,
                    'nomor_surat_otomatis' => $s->nomor_surat_otomatis,
                    'penerima'             => $s->penerima,
                    'perihal'              => $s->perihal,
                    'tanggal'              => $s->tanggal?->format('Y-m-d'),
                    'sifat'                => $s->sifat,
                ], $data->items())
                : [],
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/laporan/kinerja
     * Data kinerja pegawai — skor gabungan per user (read-only, cocok untuk tabel mobile).
     */
    public function kinerja(Request $request): JsonResponse
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $bulan   = (int) substr($periode, 5, 2);
        $tahun   = (int) substr($periode, 0, 4);

        $users = User::where('is_active', true)
            ->with(['roles', 'unitKerja'])
            ->get()
            ->map(function ($user) use ($bulan, $tahun) {
                $volume = Disposisi::where('dari_user_id', $user->id)
                    ->whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun)
                    ->count();

                $totalDisp = Disposisi::whereHas('penerima', fn($q) => $q->where('users.id', $user->id))
                    ->whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun)
                    ->count();

                $selesai = Disposisi::whereHas('penerima', fn($q) => $q->where('users.id', $user->id))
                    ->where('status', 'selesai')
                    ->whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun)
                    ->count();

                $ketuntasan  = $totalDisp > 0 ? round($selesai / $totalDisp * 100) : 0;
                $skorGabungan = round($volume * 0.2 + $ketuntasan * 0.8);

                return [
                    'user_id'     => $user->id,
                    'nama'        => $user->nama_lengkap,
                    'jabatan'     => $user->jabatan,
                    'unit_kerja'  => $user->unitKerja?->nama,
                    'volume'      => $volume,
                    'total_disp'  => $totalDisp,
                    'selesai'     => $selesai,
                    'ketuntasan'  => $ketuntasan,
                    'skor'        => $skorGabungan,
                ];
            })
            ->sortByDesc('skor')
            ->values();

        return response()->json([
            'data'    => $users,
            'periode' => $periode,
        ]);
    }
}
