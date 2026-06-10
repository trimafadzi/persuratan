<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\Disposisi;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Stat cards
        $stats = [
            'surat_belum_dibaca'    => SuratMasuk::where('status', 'belum_dibaca')->count(),
            'disposisi_pending'     => $this->getDisposisiPendingCount($user),
            'deadline_hari_ini'     => $this->getDeadlineHariIni($user),
            'surat_selesai_bulan'   => SuratMasuk::where('status', 'selesai')
                                            ->whereMonth('updated_at', now()->month)
                                            ->count(),
        ];

        // Surat masuk terbaru (5 terakhir)
        $suratMasukTerbaru = SuratMasuk::orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Aktivitas terkini dari log
        $aktivitasTerkini = $this->getAktivitasTerkini($user);

        // Disposisi deadline minggu ini
        $deadlineMingguIni = Disposisi::whereNotNull('tanggal_deadline')
            ->whereBetween('tanggal_deadline', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotIn('status', ['selesai', 'dibatalkan'])
            ->whereHas('penerima', fn($q) => $q->where('users.id', $user->id))
            ->orderBy('tanggal_deadline')
            ->limit(5)
            ->get();

        // Jumlah notifikasi untuk badge sidebar
        $jumlahBelumDibaca    = $stats['surat_belum_dibaca'];
        $jumlahDisposisiPending = $stats['disposisi_pending'];

        return view('dashboard', compact(
            'stats',
            'suratMasukTerbaru',
            'aktivitasTerkini',
            'deadlineMingguIni',
            'jumlahBelumDibaca',
            'jumlahDisposisiPending'
        ));
    }

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

    private function getAktivitasTerkini($user): array
    {
        $logs = LogAktivitas::where('user_id', $user->id)
            ->orderByDesc('timestamp')
            ->limit(6)
            ->get();

        return $logs->map(function ($log) {
            return [
                'judul'  => $this->formatActionLabel($log->action),
                'waktu'  => $log->timestamp->diffForHumans(),
                'icon'   => $this->getActionIcon($log->action),
                'warna'  => $this->getActionColor($log->action),
            ];
        })->toArray();
    }

    private function formatActionLabel(string $action): string
    {
        $map = [
            'POST surat-masuk'  => 'Input surat masuk baru',
            'POST disposisi'    => 'Buat disposisi',
            'POST surat-keluar' => 'Buat surat keluar',
            'POST draft'        => 'Buat draft surat',
            'DELETE'            => 'Hapus data',
        ];
        foreach ($map as $key => $label) {
            if (str_contains($action, $key)) return $label;
        }
        return $action;
    }

    private function getActionIcon(string $action): string
    {
        if (str_contains($action, 'surat-masuk'))  return 'bi bi-inbox-fill';
        if (str_contains($action, 'disposisi'))    return 'bi bi-diagram-3-fill';
        if (str_contains($action, 'surat-keluar')) return 'bi bi-send-fill';
        if (str_contains($action, 'draft'))        return 'bi bi-file-earmark-text';
        if (str_contains($action, 'DELETE'))       return 'bi bi-trash3';
        return 'bi bi-activity';
    }

    private function getActionColor(string $action): string
    {
        if (str_contains($action, 'DELETE')) return 'danger';
        if (str_contains($action, 'surat-masuk')) return 'primary';
        if (str_contains($action, 'disposisi')) return 'warning';
        return 'success';
    }
}
