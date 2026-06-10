<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuratKeluarResource;
use App\Models\SuratKeluar;
use App\Models\LogAktivitas;
use App\Services\NomorSuratService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratKeluarApiController extends Controller
{
    /**
     * GET /api/v1/surat-keluar
     * List surat keluar (paginated, searchable).
     */
    public function index(Request $request): JsonResponse
    {
        $query = SuratKeluar::with(['creator'])->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_surat_otomatis', 'like', "%{$request->search}%")
                  ->orWhere('penerima', 'like', "%{$request->search}%")
                  ->orWhere('perihal', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('sifat')) {
            $query->where('sifat', $request->sifat);
        }
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('tanggal', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('tanggal', '<=', $request->sampai_tanggal);
        }

        $perPage = in_array($request->per_page, [10, 25, 50, 100])
            ? (int) $request->per_page
            : 25;

        $surat = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data'  => SuratKeluarResource::collection($surat->items()),
            'meta'  => [
                'current_page' => $surat->currentPage(),
                'last_page'    => $surat->lastPage(),
                'per_page'     => $surat->perPage(),
                'total'        => $surat->total(),
            ],
            'links' => [
                'prev' => $surat->previousPageUrl(),
                'next' => $surat->nextPageUrl(),
            ],
        ]);
    }

    /**
     * POST /api/v1/surat-keluar
     * Buat surat keluar baru — nomor otomatis di-generate oleh NomorSuratService.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'penerima'   => 'required|string|max:300',
            'perihal'    => 'required|string|max:500',
            'sifat'      => 'required|in:biasa,penting,rahasia,segera',
            'isi'        => 'nullable|string',
            'file_surat' => 'nullable|file|mimes:pdf,doc,docx|max:76800',
        ], [
            'penerima.required' => 'Penerima surat wajib diisi.',
            'perihal.required'  => 'Perihal surat wajib diisi.',
            'sifat.required'    => 'Sifat surat wajib dipilih.',
        ]);

        $unitKode   = Auth::user()->unitKerja?->kode ?? 'RSU';
        $nomorSurat = app(NomorSuratService::class)->generateNomorSuratKeluar($unitKode);

        $filePath = null;
        if ($request->hasFile('file_surat')) {
            $filePath = $request->file('file_surat')->store('surat-keluar', 'public');
        }

        $surat = SuratKeluar::create([
            'nomor_surat_otomatis' => $nomorSurat,
            'tanggal'    => today(),
            'penerima'   => $validated['penerima'],
            'perihal'    => $validated['perihal'],
            'sifat'      => $validated['sifat'],
            'isi'        => $validated['isi'] ?? null,
            'file_path'  => $filePath,
            'status'     => 'approved',
            'created_by' => Auth::id(),
        ]);

        LogAktivitas::create([
            'user_id'     => Auth::id(),
            'action'      => 'POST api/surat-keluar',
            'entity_type' => 'SuratKeluar',
            'entity_id'   => $surat->id,
            'detail'      => json_encode(['nomor' => $nomorSurat, 'source' => 'mobile']),
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'timestamp'   => now(),
        ]);

        $surat->load(['creator']);

        return response()->json([
            'message' => "Surat keluar berhasil dibuat. Nomor: {$nomorSurat}",
            'data'    => new SuratKeluarResource($surat),
        ], 201);
    }

    /**
     * GET /api/v1/surat-keluar/{id}
     */
    public function show(int $id): JsonResponse
    {
        $surat = SuratKeluar::with(['creator'])->findOrFail($id);

        return response()->json([
            'data' => new SuratKeluarResource($surat),
        ]);
    }

    /**
     * PUT /api/v1/surat-keluar/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $surat = SuratKeluar::findOrFail($id);

        $validated = $request->validate([
            'penerima'   => 'required|string|max:300',
            'perihal'    => 'required|string|max:500',
            'sifat'      => 'required|in:biasa,penting,rahasia,segera',
            'isi'        => 'nullable|string',
            'file_surat' => 'nullable|file|mimes:pdf,doc,docx|max:76800',
        ]);

        $filePath = $surat->file_path;
        if ($request->hasFile('file_surat')) {
            if ($filePath) Storage::disk('public')->delete($filePath);
            $filePath = $request->file('file_surat')->store('surat-keluar', 'public');
        }

        $surat->update(array_merge($validated, ['file_path' => $filePath]));

        $surat->load(['creator']);

        return response()->json([
            'message' => 'Surat keluar berhasil diperbarui.',
            'data'    => new SuratKeluarResource($surat),
        ]);
    }

    /**
     * DELETE /api/v1/surat-keluar/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $surat = SuratKeluar::findOrFail($id);

        if ($surat->file_path) Storage::disk('public')->delete($surat->file_path);
        $surat->delete();

        return response()->json([
            'message' => 'Surat keluar berhasil dihapus.',
        ]);
    }
}
