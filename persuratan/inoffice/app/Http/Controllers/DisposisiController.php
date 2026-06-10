<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Services\FcmNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisposisiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab  = $request->get('tab', 'masuk');

        if ($tab === 'masuk') {
            $query = Disposisi::with(['suratMasuk', 'pemberi', 'penerima'])
                ->whereHas('penerima', fn($q) => $q->where('users.id', $user->id))
                ->orderByDesc('created_at');
        } else {
            $query = Disposisi::with(['suratMasuk', 'penerima'])
                ->where('dari_user_id', $user->id)
                ->orderByDesc('created_at');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('isi_disposisi', 'like', "%{$request->search}%");
        }

        $disposisiList      = $query->paginate(25)->withQueryString();
        $jumlahBelumDibaca  = SuratMasuk::where('status', 'belum_dibaca')->count();
        $jumlahDisposisiPending = Disposisi::where('status', 'pending')
            ->whereHas('penerima', fn($q) => $q->where('users.id', $user->id))->count();

        return view('disposisi.index', compact('disposisiList', 'tab', 'jumlahBelumDibaca', 'jumlahDisposisiPending'));
    }

    public function create(Request $request)
    {
        $suratMasukId = $request->get('surat_masuk_id');
        $suratMasuk   = $suratMasukId ? SuratMasuk::findOrFail($suratMasukId) : null;
        $userList     = User::where('is_active', true)
                            ->where('id', '!=', Auth::id())
                            ->orderBy('nama_lengkap')->get();
        $suratMasukList = SuratMasuk::whereIn('status', ['dibaca', 'belum_dibaca'])
                            ->orderByDesc('tanggal_terima')->get();
        $jumlahBelumDibaca = SuratMasuk::where('status', 'belum_dibaca')->count();
        $jumlahDisposisiPending = 0;

        return view('disposisi.create', compact(
            'suratMasuk', 'userList', 'suratMasukList', 'jumlahBelumDibaca', 'jumlahDisposisiPending'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'surat_masuk_id'   => 'required|exists:surat_masuk,id',
            'isi_disposisi'    => 'required|string',
            'penerima_ids'     => 'required|array|min:1',
            'penerima_ids.*'   => 'exists:users,id',
            'tanggal_deadline' => 'nullable|date|after_or_equal:today',
        ], [
            'surat_masuk_id.required' => 'Pilih surat yang akan didisposisi.',
            'isi_disposisi.required'  => 'Isi disposisi wajib diisi.',
            'penerima_ids.required'   => 'Pilih minimal 1 penerima disposisi.',
        ]);

        $disposisi = Disposisi::create([
            'surat_masuk_id'   => $validated['surat_masuk_id'],
            'dari_user_id'     => Auth::id(),
            'isi_disposisi'    => $validated['isi_disposisi'],
            'status'           => 'pending',
            'tanggal_deadline' => $validated['tanggal_deadline'] ?? null,
        ]);

        // Attach penerima
        $penerimaData = collect($validated['penerima_ids'])
            ->mapWithKeys(fn($id) => [$id => ['is_read' => false, 'created_at' => now(), 'updated_at' => now()]])
            ->toArray();
        $disposisi->penerima()->attach($penerimaData);

        // Update status surat menjadi didisposisi
        $suratMasuk = SuratMasuk::find($validated['surat_masuk_id']);
        $suratMasuk->update(['status' => 'didisposisi']);

        // Kirim notifikasi ke penerima
        $fcmService = app(FcmNotificationService::class);
        foreach ($validated['penerima_ids'] as $userId) {
            Notifikasi::create([
                'user_id'     => $userId,
                'judul'       => 'Disposisi Baru',
                'pesan'       => 'Anda menerima disposisi dari ' . Auth::user()->display_name,
                'tipe'        => 'disposisi',
                'entity_type' => 'Disposisi',
                'entity_id'   => $disposisi->id,
            ]);

            // Push notification via FCM
            $penerima = User::find($userId);
            if ($penerima) {
                $fcmService->notifyDisposisiBaru($penerima, $suratMasuk->perihal, $disposisi->id);
            }
        }

        LogAktivitas::create([
            'user_id' => Auth::id(), 'action' => 'POST disposisi',
            'entity_type' => 'Disposisi', 'entity_id' => $disposisi->id,
            'detail' => ['penerima_count' => count($validated['penerima_ids'])],
            'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'timestamp' => now(),
        ]);

        return redirect()->route('disposisi.show', $disposisi->id)
            ->with('success', 'Disposisi berhasil dikirim.');
    }

    public function show(Disposisi $disposisi)
    {
        $disposisi->load(['suratMasuk', 'pemberi', 'penerima', 'laporan.pelapor', 'laporan.fileBukti', 'children.penerima']);

        // Tandai sudah dibaca
        $disposisi->penerima()
            ->where('users.id', Auth::id())
            ->wherePivot('is_read', false)
            ->each(fn($u) => $disposisi->penerima()->updateExistingPivot($u->id, ['is_read' => true, 'read_at' => now()]));

        $jumlahBelumDibaca    = SuratMasuk::where('status', 'belum_dibaca')->count();
        $jumlahDisposisiPending = Disposisi::where('status', 'pending')
            ->whereHas('penerima', fn($q) => $q->where('users.id', Auth::id()))->count();

        return view('disposisi.show', compact('disposisi', 'jumlahBelumDibaca', 'jumlahDisposisiPending'));
    }

    public function edit(Disposisi $disposisi) { return redirect()->route('disposisi.show', $disposisi); }
    public function update(Request $request, Disposisi $disposisi) { return redirect()->route('disposisi.show', $disposisi); }

    public function destroy(Disposisi $disposisi)
    {
        $disposisi->delete();
        return redirect()->route('disposisi.index')->with('success', 'Disposisi dihapus.');
    }

    public function teruskan(Request $request, Disposisi $disposisi)
    {
        $validated = $request->validate([
            'isi_disposisi'    => 'required|string',
            'penerima_ids'     => 'required|array|min:1',
            'penerima_ids.*'   => 'exists:users,id',
            'tanggal_deadline' => 'nullable|date',
        ]);

        $child = Disposisi::create([
            'surat_masuk_id'     => $disposisi->surat_masuk_id,
            'dari_user_id'       => Auth::id(),
            'isi_disposisi'      => $validated['isi_disposisi'],
            'status'             => 'pending',
            'tanggal_deadline'   => $validated['tanggal_deadline'] ?? null,
            'parent_disposisi_id'=> $disposisi->id,
        ]);

        $child->penerima()->attach(
            collect($validated['penerima_ids'])
                ->mapWithKeys(fn($id) => [$id => ['is_read' => false, 'created_at' => now(), 'updated_at' => now()]])
                ->toArray()
        );

        $disposisi->update(['status' => 'diteruskan']);

        $fcmService = app(FcmNotificationService::class);
        foreach ($validated['penerima_ids'] as $userId) {
            Notifikasi::create([
                'user_id' => $userId, 'judul' => 'Disposisi Diteruskan',
                'pesan'   => 'Anda menerima disposisi lanjutan dari ' . Auth::user()->display_name,
                'tipe' => 'disposisi', 'entity_type' => 'Disposisi', 'entity_id' => $child->id,
            ]);

            // Push notification via FCM
            $penerima = User::find($userId);
            if ($penerima) {
                $fcmService->notifyDisposisiDiteruskan($penerima, $disposisi->suratMasuk->perihal, $child->id);
            }
        }

        return redirect()->route('disposisi.show', $child->id)
            ->with('success', 'Disposisi berhasil diteruskan.');
    }

    public function simpanLaporan(Request $request, Disposisi $disposisi)
    {
        $request->validate([
            'isi_laporan' => 'required|string',
            'file_bukti.*' => 'nullable|file|max:76800',
        ]);

        $laporan = $disposisi->laporan()->create([
            'dari_user_id' => Auth::id(),
            'isi_laporan'  => $request->isi_laporan,
            'status'       => 'terkirim',
        ]);

        if ($request->hasFile('file_bukti')) {
            foreach ($request->file('file_bukti') as $file) {
                $path = $file->store('laporan-bukti', 'public');
                $laporan->fileBukti()->create(['file_path' => $path, 'file_name' => $file->getClientOriginalName()]);
            }
        }

        // Notifikasi ke pemberi disposisi
        Notifikasi::create([
            'user_id' => $disposisi->dari_user_id, 'judul' => 'Laporan Disposisi Diterima',
            'pesan'   => Auth::user()->display_name . ' telah mengirim laporan pelaksanaan.',
            'tipe' => 'laporan', 'entity_type' => 'Disposisi', 'entity_id' => $disposisi->id,
        ]);

        // Push notification via FCM
        $pemberiDisposisi = User::find($disposisi->dari_user_id);
        if ($pemberiDisposisi) {
            $fcmService = app(FcmNotificationService::class);
            $fcmService->notifyLaporanDisposisi($pemberiDisposisi, $disposisi->suratMasuk->perihal, $laporan->id);
        }

        return back()->with('success', 'Laporan berhasil dikirim.');
    }

    public function tanggapi(Request $request, Disposisi $disposisi)
    {
        $request->validate([
            'tanggapan'        => 'required|string',
            'status_tanggapan' => 'required|in:approved,rejected',
        ]);

        $laporan = $disposisi->laporan()->where('status', 'terkirim')->latest()->first();
        if ($laporan) {
            $laporan->update([
                'tanggapan'        => $request->tanggapan,
                'status_tanggapan' => $request->status_tanggapan,
                'ditanggapi_oleh'  => Auth::id(),
                'ditanggapi_at'    => now(),
            ]);
        }

        if ($request->status_tanggapan === 'approved') {
            $disposisi->update(['status' => 'selesai']);
            // Cek apakah semua disposisi surat selesai → update surat jadi selesai
            $surat = $disposisi->suratMasuk;
            $allDone = $surat->disposisi()->whereNotIn('status', ['selesai', 'dibatalkan'])->doesntExist();
            if ($allDone) $surat->update(['status' => 'selesai']);
        }

        return back()->with('success', 'Tanggapan berhasil dikirim.');
    }

    public function batalkan(Disposisi $disposisi)
    {
        if ($disposisi->dari_user_id !== Auth::id()) {
            return back()->with('error', 'Tidak dapat membatalkan disposisi milik orang lain.');
        }
        $disposisi->update(['status' => 'dibatalkan']);
        return back()->with('success', 'Disposisi dibatalkan.');
    }
}
