<?php

namespace App\Http\Controllers;

use App\Models\DraftSurat;
use App\Models\DraftVersion;
use App\Models\TemplateSurat;
use App\Models\SuratKeluar;
use App\Services\NomorSuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DraftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'saya');

        if ($tab === 'saya') {
            $draftList = DraftSurat::with(['template', 'creator'])
                ->where('created_by', $user->id)
                ->orderByDesc('updated_at')
                ->paginate(15);
        } else {
            // tab 'persetujuan' (reviewing drafts submitted by others)
            $draftList = DraftSurat::with(['template', 'creator'])
                ->where('status', 'review')
                ->orderByDesc('updated_at')
                ->paginate(15);
        }

        return view('draft.index', compact('draftList', 'tab'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $templates = TemplateSurat::where('is_active', true)->get();
        return view('draft.create', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'template_id' => 'nullable|exists:template_surat,id',
            'konten_html' => 'required|string',
        ]);

        $draft = DraftSurat::create([
            'judul' => $request->judul,
            'template_id' => $request->template_id,
            'konten_html' => $request->konten_html,
            'status' => 'draft',
            'version' => 1,
            'created_by' => Auth::id(),
        ]);

        DraftVersion::create([
            'draft_id' => $draft->id,
            'konten_html' => $request->konten_html,
            'versi_ke' => 1,
            'catatan_perubahan' => 'Draft Awal',
            'saved_by' => Auth::id(),
        ]);

        return redirect()->route('draft.edit', $draft->id)->with('success', 'Draft berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show(DraftSurat $draft)
    {
        return view('draft.show', compact('draft'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DraftSurat $draft)
    {
        $templates = TemplateSurat::where('is_active', true)->get();
        return view('draft.edit', compact('draft', 'templates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DraftSurat $draft)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten_html' => 'required|string',
            'catatan_perubahan' => 'nullable|string',
        ]);

        $version = $draft->version + 1;

        $draft->update([
            'judul' => $request->judul,
            'konten_html' => $request->konten_html,
            'version' => $version,
            'status' => $draft->status === 'revisi' ? 'draft' : $draft->status, // Reset to draft if edited from revision
        ]);

        DraftVersion::create([
            'draft_id' => $draft->id,
            'konten_html' => $request->konten_html,
            'versi_ke' => $version,
            'catatan_perubahan' => $request->catatan_perubahan ?? "Pembaruan Versi {$version}",
            'saved_by' => Auth::id(),
        ]);

        return redirect()->route('draft.edit', $draft->id)->with('success', 'Draft berhasil disimpan!');
    }

    /**
     * Submit draft for review.
     */
    public function submitReview(DraftSurat $draft)
    {
        $draft->update(['status' => 'review']);
        return redirect()->route('draft.index')->with('success', 'Draft berhasil dikirim untuk di-review!');
    }

    /**
     * Approve draft and generate Outgoing Letter (Surat Keluar).
     */
    public function approve(Request $request, DraftSurat $draft, NomorSuratService $nomorService)
    {
        $request->validate([
            'penerima' => 'required|string|max:255',
            'sifat' => 'required|in:biasa,penting,rahasia,segera',
        ]);

        $draft->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        $unitKerja = Auth::user()->unitKerja;
        $unitKode = $unitKerja ? ($unitKerja->kode ?? 'RSU') : 'RSU';
        $nomorSurat = $nomorService->generateNomorSuratKeluar($unitKode);

        SuratKeluar::create([
            'nomor_surat_otomatis' => $nomorSurat,
            'tanggal' => now(),
            'penerima' => $request->penerima,
            'perihal' => $draft->judul,
            'sifat' => $request->sifat,
            'isi' => $draft->konten_html,
            'status' => 'approved',
            'draft_id' => $draft->id,
            'created_by' => $draft->created_by,
            'approved_by' => Auth::id(),
        ]);

        return redirect()->route('draft.index')->with('success', 'Draft berhasil disetujui dan telah diterbitkan sebagai Surat Keluar Resmi!');
    }

    /**
     * Reject/request revision for a draft.
     */
    public function revisi(Request $request, DraftSurat $draft)
    {
        $request->validate([
            'catatan' => 'required|string',
        ]);

        $draft->update([
            'status' => 'revisi',
            'reviewed_by' => Auth::id(),
        ]);

        $version = $draft->version + 1;
        $draft->update(['version' => $version]);

        DraftVersion::create([
            'draft_id' => $draft->id,
            'konten_html' => $draft->konten_html,
            'versi_ke' => $version,
            'catatan_perubahan' => 'Catatan Revisi: ' . $request->catatan,
            'saved_by' => Auth::id(),
        ]);

        return redirect()->route('draft.index')->with('success', 'Draft dikembalikan dengan catatan revisi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DraftSurat $draft)
    {
        $draft->delete();
        return redirect()->route('draft.index')->with('success', 'Draft berhasil dihapus.');
    }
}

