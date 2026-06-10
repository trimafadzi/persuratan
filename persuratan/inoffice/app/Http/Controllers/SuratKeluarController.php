<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\UnitKerja;
use App\Models\LogAktivitas;
use App\Services\NomorSuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        $query = SuratKeluar::with(['creator'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nomor_surat_otomatis', 'like', "%{$request->search}%")
                  ->orWhere('penerima', 'like', "%{$request->search}%")
                  ->orWhere('perihal', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('sifat'))  $query->where('sifat', $request->sifat);

        $perPage    = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $suratList  = $query->paginate($perPage)->withQueryString();
        $jumlahBelumDibaca = \App\Models\SuratMasuk::where('status', 'belum_dibaca')->count();
        $jumlahDisposisiPending = 0;

        return view('surat-keluar.index', compact('suratList', 'jumlahBelumDibaca', 'jumlahDisposisiPending'));
    }

    public function create()
    {
        $unitKerjaList = UnitKerja::where('is_active', true)->orderBy('nama')->get();
        $user = Auth::user()->load('unitKerja');
        $jumlahBelumDibaca = \App\Models\SuratMasuk::where('status', 'belum_dibaca')->count();
        $jumlahDisposisiPending = 0;
        return view('surat-keluar.create', compact('unitKerjaList', 'user', 'jumlahBelumDibaca', 'jumlahDisposisiPending'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'penerima'  => 'required|string|max:300',
            'perihal'   => 'required|string|max:500',
            'sifat'     => 'required|in:biasa,penting,rahasia,segera',
            'isi'       => 'nullable|string',
            'file_surat'=> 'nullable|file|mimes:pdf,doc,docx|max:76800',
        ]);

        $unitKode = Auth::user()->unitKerja?->kode ?? 'RSU';
        $nomorSurat = app(NomorSuratService::class)->generateNomorSuratKeluar($unitKode);

        $filePath = null;
        if ($request->hasFile('file_surat')) {
            $filePath = $request->file('file_surat')->store('surat-keluar', 'public');
        }

        $surat = SuratKeluar::create([
            'nomor_surat_otomatis' => $nomorSurat,
            'tanggal'   => today(),
            'penerima'  => $validated['penerima'],
            'perihal'   => $validated['perihal'],
            'sifat'     => $validated['sifat'],
            'isi'       => $validated['isi'] ?? null,
            'file_path' => $filePath,
            'status'    => 'approved',
            'created_by'=> Auth::id(),
        ]);

        LogAktivitas::create([
            'user_id' => Auth::id(), 'action' => 'POST surat-keluar',
            'entity_type' => 'SuratKeluar', 'entity_id' => $surat->id,
            'detail' => ['nomor' => $nomorSurat], 'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(), 'timestamp' => now(),
        ]);

        return redirect()->route('surat-keluar.show', $surat->id)
            ->with('success', "Surat keluar berhasil dibuat. Nomor: {$nomorSurat}");
    }

    public function show(SuratKeluar $suratKeluar)
    {
        $jumlahBelumDibaca    = \App\Models\SuratMasuk::where('status', 'belum_dibaca')->count();
        $jumlahDisposisiPending = 0;
        return view('surat-keluar.show', compact('suratKeluar', 'jumlahBelumDibaca', 'jumlahDisposisiPending'));
    }

    public function edit(SuratKeluar $suratKeluar)
    {
        $jumlahBelumDibaca = \App\Models\SuratMasuk::where('status', 'belum_dibaca')->count();
        $jumlahDisposisiPending = 0;
        return view('surat-keluar.edit', compact('suratKeluar', 'jumlahBelumDibaca', 'jumlahDisposisiPending'));
    }

    public function update(Request $request, SuratKeluar $suratKeluar)
    {
        $validated = $request->validate([
            'penerima'  => 'required|string|max:300',
            'perihal'   => 'required|string|max:500',
            'sifat'     => 'required|in:biasa,penting,rahasia,segera',
            'isi'       => 'nullable|string',
            'file_surat'=> 'nullable|file|mimes:pdf,doc,docx|max:76800',
        ]);

        $filePath = $suratKeluar->file_path;
        if ($request->hasFile('file_surat')) {
            if ($filePath) Storage::disk('public')->delete($filePath);
            $filePath = $request->file('file_surat')->store('surat-keluar', 'public');
        }

        $suratKeluar->update(array_merge($validated, ['file_path' => $filePath]));
        return redirect()->route('surat-keluar.show', $suratKeluar->id)
            ->with('success', 'Surat keluar berhasil diperbarui.');
    }

    public function destroy(SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->file_path) Storage::disk('public')->delete($suratKeluar->file_path);
        $suratKeluar->delete();
        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar dihapus.');
    }
}
