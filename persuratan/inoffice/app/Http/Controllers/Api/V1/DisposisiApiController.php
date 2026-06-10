<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DisposisiResource;
use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Models\Notifikasi;
use App\Models\LogAktivitas;
use App\Services\FcmNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DisposisiApiController extends Controller
{
    /**
     * GET /api/v1/disposisi
     * List disposisi — tab masuk (default) atau keluar.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $tab  = $request->get('tab', 'masuk'); // masuk | keluar

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

        $perPage = in_array($request->per_page, [10, 25, 50]) ? (int) $request->per_page : 25;
        $list    = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data'  => DisposisiResource::collection($list->items()),
            'meta'  => [
                'current_page' => $list->currentPage(),
                'last_page'    => $list->lastPage(),
                'per_page'     => $list->perPage(),
                'total'        => $list->total(),
                'tab'          => $tab,
            ],
            'links' => [
                'prev' => $list->previousPageUrl(),
                'next' => $list->nextPageUrl(),
            ],
        ]);
    }

    /**
     * POST /api/v1/disposisi
     * Buat disposisi baru.
     */
    public function store(Request $request): JsonResponse
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
            'user_id'     => Auth::id(),
            'action'      => 'POST api/disposisi',
            'entity_type' => 'Disposisi',
            'entity_id'   => $disposisi->id,
            'detail'      => json_encode(['penerima_count' => count($validated['penerima_ids']), 'source' => 'mobile']),
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'timestamp'   => now(),
        ]);

        $disposisi->load(['suratMasuk', 'pemberi', 'penerima']);

        return response()->json([
            'message' => 'Disposisi berhasil dikirim.',
            'data'    => new DisposisiResource($disposisi),
        ], 201);
    }

    /**
     * GET /api/v1/disposisi/{id}
     * Detail disposisi — tandai dibaca secara otomatis untuk penerima.
     */
    public function show(int $id): JsonResponse
    {
        $disposisi = Disposisi::with([
            'suratMasuk', 'pemberi', 'penerima',
            'laporan.pelapor', 'laporan.fileBukti',
            'children.penerima',
        ])->findOrFail($id);

        // Tandai sudah dibaca untuk user yang sedang login
        $disposisi->penerima()
            ->where('users.id', Auth::id())
            ->wherePivot('is_read', false)
            ->each(fn($u) => $disposisi->penerima()->updateExistingPivot($u->id, [
                'is_read' => true,
                'read_at' => now(),
            ]));

        return response()->json([
            'data' => new DisposisiResource($disposisi),
        ]);
    }

    /**
     * POST /api/v1/disposisi/{id}/teruskan
     * Teruskan disposisi ke penerima lain (cascade).
     */
    public function teruskan(Request $request, int $id): JsonResponse
    {
        $disposisi = Disposisi::findOrFail($id);

        $validated = $request->validate([
            'isi_disposisi'    => 'required|string',
            'penerima_ids'     => 'required|array|min:1',
            'penerima_ids.*'   => 'exists:users,id',
            'tanggal_deadline' => 'nullable|date',
        ]);

        $child = Disposisi::create([
            'surat_masuk_id'      => $disposisi->surat_masuk_id,
            'dari_user_id'        => Auth::id(),
            'isi_disposisi'       => $validated['isi_disposisi'],
            'status'              => 'pending',
            'tanggal_deadline'    => $validated['tanggal_deadline'] ?? null,
            'parent_disposisi_id' => $disposisi->id,
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
                'user_id'     => $userId,
                'judul'       => 'Disposisi Diteruskan',
                'pesan'       => 'Anda menerima disposisi lanjutan dari ' . Auth::user()->display_name,
                'tipe'        => 'disposisi',
                'entity_type' => 'Disposisi',
                'entity_id'   => $child->id,
            ]);

            // Push notification via FCM
            $penerima = User::find($userId);
            if ($penerima) {
                $fcmService->notifyDisposisiDiteruskan($penerima, $disposisi->suratMasuk->perihal, $child->id);
            }
        }

        $child->load(['suratMasuk', 'pemberi', 'penerima']);

        return response()->json([
            'message' => 'Disposisi berhasil diteruskan.',
            'data'    => new DisposisiResource($child),
        ], 201);
    }

    /**
     * POST /api/v1/disposisi/{id}/laporan
     * Submit laporan pelaksanaan disposisi + upload file bukti.
     */
    public function simpanLaporan(Request $request, int $id): JsonResponse
    {
        $disposisi = Disposisi::findOrFail($id);

        $request->validate([
            'isi_laporan'  => 'required|string',
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
                $laporan->fileBukti()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        // Notifikasi ke pemberi disposisi
        Notifikasi::create([
            'user_id'     => $disposisi->dari_user_id,
            'judul'       => 'Laporan Disposisi Diterima',
            'pesan'       => Auth::user()->display_name . ' telah mengirim laporan pelaksanaan.',
            'tipe'        => 'laporan',
            'entity_type' => 'Disposisi',
            'entity_id'   => $disposisi->id,
        ]);

        // Push notification via FCM
        $pemberiDisposisi = User::find($disposisi->dari_user_id);
        if ($pemberiDisposisi) {
            $fcmService = app(FcmNotificationService::class);
            $fcmService->notifyLaporanDisposisi($pemberiDisposisi, $disposisi->suratMasuk->perihal, $laporan->id);
        }

        return response()->json([
            'message'    => 'Laporan berhasil dikirim.',
            'laporan_id' => $laporan->id,
        ], 201);
    }

    /**
     * POST /api/v1/disposisi/{id}/tanggapi
     * Approve atau reject laporan disposisi.
     */
    public function tanggapi(Request $request, int $id): JsonResponse
    {
        $disposisi = Disposisi::findOrFail($id);

        $request->validate([
            'tanggapan'        => 'required|string',
            'status_tanggapan' => 'required|in:approved,rejected',
        ]);

        $laporan = $disposisi->laporan()->where('status', 'terkirim')->latest()->first();

        if (!$laporan) {
            return response()->json([
                'message' => 'Tidak ada laporan yang menunggu tanggapan.',
            ], 422);
        }

        $laporan->update([
            'tanggapan'        => $request->tanggapan,
            'status_tanggapan' => $request->status_tanggapan,
            'ditanggapi_oleh'  => Auth::id(),
            'ditanggapi_at'    => now(),
        ]);

        if ($request->status_tanggapan === 'approved') {
            $disposisi->update(['status' => 'selesai']);

            // Cek apakah semua disposisi surat selesai → update surat jadi selesai
            $surat   = $disposisi->suratMasuk;
            $allDone = $surat->disposisi()->whereNotIn('status', ['selesai', 'dibatalkan'])->doesntExist();
            if ($allDone) $surat->update(['status' => 'selesai']);
        }

        return response()->json([
            'message' => 'Tanggapan berhasil dikirim.',
            'status'  => $request->status_tanggapan,
        ]);
    }

    /**
     * PATCH /api/v1/disposisi/{id}/batal
     * Batalkan disposisi (hanya oleh pembuat).
     */
    public function batalkan(int $id): JsonResponse
    {
        $disposisi = Disposisi::findOrFail($id);

        if ($disposisi->dari_user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Tidak dapat membatalkan disposisi milik orang lain.',
            ], 403);
        }

        $disposisi->update(['status' => 'dibatalkan']);

        return response()->json([
            'message' => 'Disposisi berhasil dibatalkan.',
        ]);
    }
}
