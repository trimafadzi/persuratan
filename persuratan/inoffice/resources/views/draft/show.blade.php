@extends('layouts.app')
@section('title', 'Detail Draft: ' . $draft->judul)
@section('page-title', 'Detail Draft & Konsep')
@section('page-subtitle', 'Tinjau konten draft surat dan lakukan persetujuan')

@section('content')
<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;max-width:1200px;margin:0 auto;align-items:start;">
    <!-- Document Content View -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:40px;box-shadow:var(--shadow-sm);min-height:500px;">
            <div style="border-bottom:2px double var(--border);padding-bottom:12px;margin-bottom:20px;text-align:center;">
                <h2 style="font-weight:700;color:var(--primary);margin-bottom:4px;">{{ $draft->judul }}</h2>
                <span style="font-size:12px;color:var(--text-muted);">Status: {{ strtoupper($draft->status) }} — Versi v{{ $draft->version }}</span>
            </div>
            
            <div style="font-family:sans-serif;font-size:14px;line-height:1.6;color:#334155;">
                {!! $draft->konten_html !!}
            </div>
        </div>
    </div>

    <!-- Actions & Metadata Sidebar -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <!-- Metadata -->
        <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm);">
            <h4 style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:12px;border-bottom:1px solid var(--border);padding-bottom:8px;">Metadata Draft</h4>
            <div style="display:flex;flex-direction:column;gap:10px;font-size:12px;">
                <div><strong>Judul:</strong> {{ $draft->judul }}</div>
                <div><strong>Template:</strong> {{ $draft->template->nama ?? 'Tanpa Template' }}</div>
                <div><strong>Versi Terakhir:</strong> v{{ $draft->version }}</div>
                <div><strong>Pembuat:</strong> {{ $draft->creator->nama_lengkap }}</div>
                <div><strong>Dibuat:</strong> {{ $draft->created_at->format('d M Y H:i') }}</div>
                @if($draft->approver)
                    <div><strong>Disetujui Oleh:</strong> {{ $draft->approver->nama_lengkap }}</div>
                @endif
            </div>
        </div>

        <!-- Approval Actions -->
        @if($draft->status === 'review' && (auth()->user()->hasPermission('draft.approve') || auth()->user()->hasPermission('draft.review')))
        <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm);">
            <h4 style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:12px;border-bottom:1px solid var(--border);padding-bottom:8px;">Persetujuan / Tindakan</h4>
            
            <!-- Approve Form -->
            <form method="POST" action="{{ route('draft.approve', $draft->id) }}" style="margin-bottom:16px;">
                @csrf
                <div style="margin-bottom:10px;">
                    <label for="penerima" style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Penerima Surat Keluar *</label>
                    <input type="text" name="penerima" id="penerima" required placeholder="Contoh: Kepala Dinas Kesehatan DKI"
                           style="width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;font-size:12px;outline:none;">
                </div>
                <div style="margin-bottom:12px;">
                    <label for="sifat" style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Sifat Surat *</label>
                    <select name="sifat" id="sifat" required style="width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;font-size:12px;outline:none;background:#fff;">
                        <option value="biasa">Biasa</option>
                        <option value="penting">Penting</option>
                        <option value="rahasia">Rahasia</option>
                        <option value="segera">Segera</option>
                    </select>
                </div>
                <button type="submit" style="width:100%;padding:9px;background:var(--success);color:#fff;border:none;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <i class="bi bi-check-circle"></i> Setujui & Terbitkan
                </button>
            </form>

            <hr style="border:none;border-top:1px solid var(--border);margin:12px 0;">

            <!-- Revisi Form -->
            <form method="POST" action="{{ route('draft.revisi', $draft->id) }}">
                @csrf
                <div style="margin-bottom:10px;">
                    <label for="catatan" style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Catatan Revisi *</label>
                    <textarea name="catatan" id="catatan" required placeholder="Jelaskan bagian yang perlu direvisi..."
                              style="width:100%;height:80px;padding:8px;border:1px solid var(--border);border-radius:6px;font-size:12px;outline:none;resize:none;"></textarea>
                </div>
                <button type="submit" style="width:100%;padding:9px;background:var(--danger);color:#fff;border:none;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <i class="bi bi-arrow-left-right"></i> Kembalikan (Revisi)
                </button>
            </form>
        </div>
        @endif

        <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm);">
            <a href="{{ route('draft.index') }}" style="display:block;text-align:center;padding:9px;background:#f1f5f9;color:var(--text);border:1px solid var(--border);border-radius:6px;font-size:13px;text-decoration:none;font-weight:600;">Kembali ke Daftar</a>
        </div>
    </div>
</div>
@endsection
