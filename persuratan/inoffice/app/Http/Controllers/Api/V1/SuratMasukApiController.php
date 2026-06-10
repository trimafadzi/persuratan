<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuratMasukResource;
use App\Http\Resources\SuratMasukCollection;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Models\Notifikasi;
use App\Models\LogAktivitas;
use App\Services\FcmNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratMasukApiController extends Controller
{
    /**
     * GET /api/v1/surat-masuk
     * List surat masuk (paginated, searchable, filterable).
     */
    public function index(Request $request): JsonResponse
    {
        $query = SuratMasuk::with(['creator', 'unitKerja'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('sifat')) {
            $query->where('sifat', $request->sifat);
        }
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('tanggal_terima', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('tanggal_terima', '<=', $request->sampai_tanggal);
        }

        $perPage = in_array($request->per_page, [10, 25, 50, 100])
            ? (int) $request->per_page
            : 25;

        $surat = $query->paginate($perPage)->withQueryString();

        return (new SuratMasukCollection($surat))->response();
    }

    /**
     * POST /api/v1/surat-masuk
     * Buat surat masuk baru (support multipart/form-data untuk file scan).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomor_surat'    => 'required|string|max:100',
            'tanggal_surat'  => 'required|date',
            'tanggal_terima' => 'required|date',
            'pengirim'       => 'required|string|max:200',
            'perihal'        => 'required|string|max:500',
            'sifat'          => 'required|in:biasa,penting,rahasia,segera',
            'ringkasan'      => 'nullable|string',
            'unit_kerja_id'  => 'nullable|exists:unit_kerja,id',
            'file_scan'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:76800',
        ], [
            'nomor_surat.required'    => 'Nomor surat wajib diisi.',
            'tanggal_surat.required'  => 'Tanggal surat wajib diisi.',
            'tanggal_terima.required' => 'Tanggal terima wajib diisi.',
            'pengirim.required'       => 'Nama pengirim wajib diisi.',
            'perihal.required'        => 'Perihal surat wajib diisi.',
            'sifat.required'          => 'Sifat surat wajib dipilih.',
            'file_scan.max'           => 'Ukuran file maksimal 75 MB.',
        ]);

        $filePath = null;
        if ($request->hasFile('file_scan')) {
            $filePath = $request->file('file_scan')->store('surat-masuk', 'public');
        }

        $surat = SuratMasuk::create([
            'nomor_surat'    => $validated['nomor_surat'],
            'tanggal_surat'  => $validated['tanggal_surat'],
            'tanggal_terima' => $validated['tanggal_terima'],
            'pengirim'       => $validated['pengirim'],
            'perihal'        => $validated['perihal'],
            'sifat'          => $validated['sifat'],
            'ringkasan'      => $validated['ringkasan'] ?? null,
            'unit_kerja_id'  => $validated['unit_kerja_id'] ?? null,
            'file_path'      => $filePath,
            'status'         => 'belum_dibaca',
            'created_by'     => Auth::id(),
        ]);

        LogAktivitas::create([
            'user_id'     => Auth::id(),
            'action'      => 'POST api/surat-masuk',
            'entity_type' => 'SuratMasuk',
            'entity_id'   => $surat->id,
            'detail'      => json_encode(['perihal' => $surat->perihal, 'source' => 'mobile']),
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'timestamp'   => now(),
        ]);

        // Notifikasi ke pimpinan (role: pimpinan, direktur, superadmin)
        $pimpinan = User::whereHas('roles', function ($q) {
            $q->whereIn('slug', ['pimpinan', 'direktur', 'superadmin']);
        })->where('is_active', true)->get();

        $fcmService = app(FcmNotificationService::class);
        foreach ($pimpinan as $p) {
            Notifikasi::create([
                'user_id'     => $p->id,
                'judul'       => 'Surat Masuk Baru',
                'pesan'       => "Surat masuk baru: {$surat->perihal}",
                'tipe'        => 'surat_masuk',
                'entity_type' => 'SuratMasuk',
                'entity_id'   => $surat->id,
            ]);

            $fcmService->notifySuratMasukBaru($p, $surat->perihal, $surat->id);
        }

        $surat->load(['creator', 'unitKerja']);

        return response()->json([
            'message' => 'Surat masuk berhasil ditambahkan.',
            'data'    => new SuratMasukResource($surat),
        ], 201);
    }

    /**
     * GET /api/v1/surat-masuk/{id}
     * Detail surat masuk beserta disposisi yang terkait.
     * Catatan: TIDAK auto-update status (berbeda dari web controller),
     * gunakan endpoint PATCH /{id}/baca untuk menandai dibaca secara eksplisit.
     */
    public function show(int $id): JsonResponse
    {
        $surat = SuratMasuk::with(['creator', 'unitKerja', 'disposisi.pemberi', 'disposisi.penerima'])
            ->findOrFail($id);

        return response()->json([
            'data' => new SuratMasukResource($surat),
        ]);
    }

    /**
     * PUT /api/v1/surat-masuk/{id}
     * Update surat masuk (termasuk ganti file scan).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $surat = SuratMasuk::findOrFail($id);

        $validated = $request->validate([
            'nomor_surat'    => 'required|string|max:100',
            'tanggal_surat'  => 'required|date',
            'tanggal_terima' => 'required|date',
            'pengirim'       => 'required|string|max:200',
            'perihal'        => 'required|string|max:500',
            'sifat'          => 'required|in:biasa,penting,rahasia,segera',
            'ringkasan'      => 'nullable|string',
            'unit_kerja_id'  => 'nullable|exists:unit_kerja,id',
            'file_scan'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:76800',
        ]);

        $filePath = $surat->file_path;
        if ($request->hasFile('file_scan')) {
            if ($filePath) Storage::disk('public')->delete($filePath);
            $filePath = $request->file('file_scan')->store('surat-masuk', 'public');
        }

        $surat->update(array_merge($validated, ['file_path' => $filePath]));

        $surat->load(['creator', 'unitKerja']);

        return response()->json([
            'message' => 'Surat masuk berhasil diperbarui.',
            'data'    => new SuratMasukResource($surat),
        ]);
    }

    /**
     * DELETE /api/v1/surat-masuk/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $surat = SuratMasuk::findOrFail($id);

        if ($surat->file_path) {
            Storage::disk('public')->delete($surat->file_path);
        }
        $surat->delete();

        return response()->json([
            'message' => 'Surat masuk berhasil dihapus.',
        ]);
    }

    /**
     * PATCH /api/v1/surat-masuk/{id}/baca
     * Tandai surat sebagai sudah dibaca (explicit action dari mobile).
     */
    public function tandaiBaca(int $id): JsonResponse
    {
        $surat = SuratMasuk::findOrFail($id);

        if ($surat->status === 'belum_dibaca') {
            $surat->update(['status' => 'dibaca']);
        }

        return response()->json([
            'message' => 'Surat ditandai sudah dibaca.',
            'data'    => ['status' => $surat->fresh()->status],
        ]);
    }
}
