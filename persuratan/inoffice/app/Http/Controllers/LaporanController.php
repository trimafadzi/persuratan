<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    private function shared(): array
    {
        return ['jumlahBelumDibaca' => SuratMasuk::where('status','belum_dibaca')->count(), 'jumlahDisposisiPending' => 0];
    }

    public function index()
    {
        // Statistik dashboard laporan
        $stats = [
            'total_masuk'   => SuratMasuk::count(),
            'total_keluar'  => SuratKeluar::count(),
            'total_disposisi'  => Disposisi::count(),
            'selesai_bulan' => SuratMasuk::where('status','selesai')->whereMonth('updated_at', now()->month)->count(),
        ];

        // Volume surat per bulan (12 bulan terakhir)
        $volumePerBulan = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $volumePerBulan[] = [
                'bulan'  => $date->format('M Y'),
                'masuk'  => SuratMasuk::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'keluar' => SuratKeluar::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ];
        }

        // Status breakdown surat masuk
        $statusBreakdown = SuratMasuk::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total','status');

        return view('laporan.index', array_merge(compact('stats','volumePerBulan','statusBreakdown'), $this->shared()));
    }

    public function suratMasuk(Request $request)
    {
        $query = SuratMasuk::with(['creator','unitKerja'])->orderByDesc('tanggal_terima');
        if ($request->filled('dari'))   $query->whereDate('tanggal_terima','>=',$request->dari);
        if ($request->filled('sampai')) $query->whereDate('tanggal_terima','<=',$request->sampai);
        if ($request->filled('status')) $query->where('status',$request->status);

        $data = $query->paginate(50)->withQueryString();
        return view('laporan.surat-masuk', array_merge(compact('data'), $this->shared()));
    }

    public function suratKeluar(Request $request)
    {
        $query = SuratKeluar::with(['creator'])->orderByDesc('tanggal');
        if ($request->filled('dari'))   $query->whereDate('tanggal','>=',$request->dari);
        if ($request->filled('sampai')) $query->whereDate('tanggal','<=',$request->sampai);

        $data = $query->paginate(50)->withQueryString();
        return view('laporan.surat-keluar', array_merge(compact('data'), $this->shared()));
    }

    public function kinerja(Request $request)
    {
        $periode  = $request->get('periode', now()->format('Y-m'));
        $bulan    = substr($periode, 5, 2);
        $tahun    = substr($periode, 0, 4);

        $users = User::where('is_active', true)->with(['roles','unitKerja'])->get()->map(function ($user) use ($bulan, $tahun) {
            // Volume: surat masuk yang ditangani
            $volume = Disposisi::where('dari_user_id', $user->id)
                ->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun)->count();

            // Ketuntasan: disposisi selesai / total
            $totalDisp  = Disposisi::whereHas('penerima', fn($q) => $q->where('users.id', $user->id))
                ->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun)->count();
            $selesai = Disposisi::whereHas('penerima', fn($q) => $q->where('users.id', $user->id))
                ->where('status','selesai')
                ->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun)->count();

            $ketuntasan = $totalDisp > 0 ? round($selesai / $totalDisp * 100) : 0;
            $skorGabungan = round($volume * 0.2 + $ketuntasan * 0.8);

            return [
                'user'        => $user,
                'volume'      => $volume,
                'ketuntasan'  => $ketuntasan,
                'skor'        => $skorGabungan,
                'total_disp'  => $totalDisp,
                'selesai'     => $selesai,
            ];
        })->sortByDesc('skor');

        return view('laporan.kinerja', array_merge(compact('users','periode'), $this->shared()));
    }

    public function export(Request $request, string $type, string $format)
    {
        // Placeholder — implementasi CSV export sederhana
        if ($format === 'csv') {
            $filename = "laporan_{$type}_" . now()->format('Ymd') . ".csv";
            $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"];

            $callback = function () use ($type) {
                $handle = fopen('php://output', 'w');
                if ($type === 'surat-masuk') {
                    fputcsv($handle, ['No', 'Nomor Surat', 'Pengirim', 'Perihal', 'Tgl Terima', 'Status', 'Sifat']);
                    SuratMasuk::orderByDesc('tanggal_terima')->each(function ($s, $i) use ($handle) {
                        fputcsv($handle, [$i+1, $s->nomor_surat, $s->pengirim, $s->perihal, $s->tanggal_terima->format('d/m/Y'), $s->status_label, ucfirst($s->sifat)]);
                    });
                } elseif ($type === 'surat-keluar') {
                    fputcsv($handle, ['No', 'Nomor Surat', 'Penerima', 'Perihal', 'Tanggal', 'Sifat']);
                    SuratKeluar::orderByDesc('tanggal')->each(function ($s, $i) use ($handle) {
                        fputcsv($handle, [$i+1, $s->nomor_surat_otomatis, $s->penerima, $s->perihal, $s->tanggal->format('d/m/Y'), ucfirst($s->sifat)]);
                    });
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Format export belum didukung.');
    }
}
