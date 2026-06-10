@extends('layouts.app')
@section('title', 'Edit Draft: ' . $draft->judul)
@section('page-title', 'Edit Draft')
@section('page-subtitle', 'Sunting konsep surat dan buat versi baru')

@section('content')
<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;max-width:1200px;margin:0 auto;align-items:start;">
    <!-- Editor Form -->
    <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:24px;box-shadow:var(--shadow-sm);">
        <form method="POST" action="{{ route('draft.update', $draft->id) }}">
            @csrf @method('PUT')
            
            <div style="margin-bottom:16px;">
                <label for="judul" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--text);">Judul / Perihal Draft *</label>
                <input type="text" name="judul" id="judul" required value="{{ old('judul', $draft->judul) }}"
                       style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;outline:none;">
                @error('judul')
                    <span style="color:var(--danger);font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:16px;">
                <label for="konten_html" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--text);">Editor Surat *</label>
                <textarea name="konten_html" id="konten_html" required style="width:100%;height:450px;padding:16px;border:1px solid var(--border);border-radius:8px;font-family:sans-serif;font-size:14px;line-height:1.6;outline:none;resize:vertical;">{{ old('konten_html', $draft->konten_html) }}</textarea>
                @error('konten_html')
                    <span style="color:var(--danger);font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:24px;">
                <label for="catatan_perubahan" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--text);">Catatan Perubahan (Opsional)</label>
                <input type="text" name="catatan_perubahan" id="catatan_perubahan" placeholder="Contoh: Menambahkan perihal rapat dan mengganti tanggal"
                       style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;outline:none;">
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;border-top:1px solid var(--border);padding-top:16px;">
                <a href="{{ route('draft.index') }}" style="padding:10px 20px;background:#f1f5f9;color:var(--text);border:1px solid var(--border);border-radius:8px;font-size:13px;text-decoration:none;font-weight:600;">Kembali</a>
                <button type="submit" style="padding:10px 20px;background:var(--primary);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <!-- Right Sidebar Info (Version History) -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm);">
            <h4 style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:12px;border-bottom:1px solid var(--border);padding-bottom:8px;">Status & Informasi</h4>
            <div style="display:flex;flex-direction:column;gap:10px;font-size:12px;">
                <div><strong>Status:</strong> 
                    @if($draft->status === 'draft')
                        <span style="padding:2px 6px;background:#f1f5f9;color:var(--text-muted);border-radius:4px;font-weight:600;">Draft</span>
                    @elseif($draft->status === 'review')
                        <span style="padding:2px 6px;background:#eff6ff;color:#1e40af;border-radius:4px;font-weight:600;">Review</span>
                    @elseif($draft->status === 'revisi')
                        <span style="padding:2px 6px;background:#fffbeb;color:#b45309;border-radius:4px;font-weight:600;">Butuh Revisi</span>
                    @elseif($draft->status === 'approved')
                        <span style="padding:2px 6px;background:#f0fdf4;color:#15803d;border-radius:4px;font-weight:600;">Approved</span>
                    @endif
                </div>
                <div><strong>Versi Aktif:</strong> <span style="font-weight:600;">v{{ $draft->version }}</span></div>
                <div><strong>Pembuat:</strong> {{ $draft->creator->nama_lengkap }}</div>
                <div><strong>Dibuat Pada:</strong> {{ $draft->created_at->format('d M Y H:i') }}</div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm);max-height:400px;overflow-y:auto;">
            <h4 style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:12px;border-bottom:1px solid var(--border);padding-bottom:8px;">Riwayat Versi</h4>
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($draft->versions()->orderByDesc('versi_ke')->get() as $ver)
                <div style="border-left:2px solid var(--primary);padding-left:8px;font-size:11px;">
                    <div style="display:flex;justify-content:space-between;font-weight:700;color:var(--primary);">
                        <span>Versi {{ $ver->versi_ke }}</span>
                        <span style="color:var(--text-muted);font-weight:400;">{{ $ver->created_at->format('d M H:i') }}</span>
                    </div>
                    <p style="margin:2px 0;color:var(--text);">{{ $ver->catatan_perubahan ?? 'Pembaruan Tanpa Catatan' }}</p>
                    <span style="color:var(--text-muted);font-size:10px;">Oleh: {{ $ver->saver->nama_lengkap ?? 'Unknown' }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
