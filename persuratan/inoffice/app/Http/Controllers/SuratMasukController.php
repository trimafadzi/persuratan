<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\UnitKerja;
use App\Models\Disposisi;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratMasukController extends Controller
{
    public function index(Request $request)
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

        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $suratList = $query->paginate($perPage)->withQueryString();

        $unitKerjaList = UnitKerja::where('is_active', true)->orderBy('nama')->get();

        $jumlahBelumDibaca    = SuratMasuk::where('status', 'belum_dibaca')->count();
        $jumlahDisposisiPending = 0;

        return view('surat-masuk.index', compact('suratList', 'unitKerjaList', 'jumlahBelumDibaca', 'jumlahDisposisiPending'));
    }

    public function create()
    {
        $unitKerjaList = UnitKerja::where('is_active', true)->orderBy('nama')->get();
        $jumlahBelumDibaca = SuratMasuk::where('status', 'belum_dibaca')->count();
        $jumlahDisposisiPending = 0;
        return view('surat-masuk.create', compact('unitKerjaList', 'jumlahBelumDibaca', 'jumlahDisposisiPending'));
    }

    public function store(Request $request)
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
            'action'      => 'POST surat-masuk',
            'entity_type' => 'SuratMasuk',
            'entity_id'   => $surat->id,
            'detail'      => ['perihal' => $surat->perihal],
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'timestamp'   => now(),
        ]);

        return redirect()->route('surat-masuk.show', $surat->id)
            ->with('success', 'Surat masuk berhasil ditambahkan.');
    }

    public function show(SuratMasuk $suratMasuk)
    {
        // Tandai sudah dibaca jika masih belum_dibaca
        if ($suratMasuk->status === 'belum_dibaca') {
            $suratMasuk->update(['status' => 'dibaca']);
        }

        $suratMasuk->load(['creator', 'unitKerja', 'disposisi.pemberi', 'disposisi.penerima']);
        $jumlahBelumDibaca    = SuratMasuk::where('status', 'belum_dibaca')->count();
        $jumlahDisposisiPending = 0;

        return view('surat-masuk.show', compact('suratMasuk', 'jumlahBelumDibaca', 'jumlahDisposisiPending'));
    }

    public function edit(SuratMasuk $suratMasuk)
    {
        $unitKerjaList = UnitKerja::where('is_active', true)->orderBy('nama')->get();
        $jumlahBelumDibaca    = SuratMasuk::where('status', 'belum_dibaca')->count();
        $jumlahDisposisiPending = 0;
        return view('surat-masuk.edit', compact('suratMasuk', 'unitKerjaList', 'jumlahBelumDibaca', 'jumlahDisposisiPending'));
    }

    public function update(Request $request, SuratMasuk $suratMasuk)
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
        ]);

        $filePath = $suratMasuk->file_path;
        if ($request->hasFile('file_scan')) {
            if ($filePath) Storage::disk('public')->delete($filePath);
            $filePath = $request->file('file_scan')->store('surat-masuk', 'public');
        }

        $suratMasuk->update(array_merge($validated, ['file_path' => $filePath]));

        return redirect()->route('surat-masuk.show', $suratMasuk->id)
            ->with('success', 'Surat masuk berhasil diperbarui.');
    }

    public function destroy(SuratMasuk $suratMasuk)
    {
        if ($suratMasuk->file_path) {
            Storage::disk('public')->delete($suratMasuk->file_path);
        }
        $suratMasuk->delete();
        return redirect()->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil dihapus.');
    }

    public function tandaiBaca(SuratMasuk $suratMasuk)
    {
        if ($suratMasuk->status === 'belum_dibaca') {
            $suratMasuk->update(['status' => 'dibaca']);
        }
        return back()->with('success', 'Surat ditandai sudah dibaca.');
    }
}
