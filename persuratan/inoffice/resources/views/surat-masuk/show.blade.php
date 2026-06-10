@extends('layouts.app')
@section('title', 'Detail Surat Masuk')
@section('page-title', 'Detail Surat Masuk')
@section('page-subtitle', $suratMasuk->perihal)

@section('content')
<style>
.detail-grid { display:grid; grid-template-columns:1fr 340px; gap:16px; }
@media(max-width:900px){.detail-grid{grid-template-columns:1fr;}}
.detail-card { background:#fff; border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; box-shadow:var(--shadow-sm); }
.card-section-title { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.8px; padding:12px 20px 8px; border-bottom:1px solid var(--border); background:#f8fafc; }
.detail-table { width:100%; border-collapse:collapse; }
.detail-table tr { border-bottom:1px solid #f1f5f9; }
.detail-table tr:last-child{border:none;}
.detail-table td { padding:11px 20px; font-size:13px; vertical-align:top; }
.detail-table td:first-child { color:var(--text-muted); font-weight:600; width:160px; white-space:nowrap; }
.detail-table td:last-child { color:var(--text); }
.status-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:100px; font-size:12px; font-weight:700; }
.status-belum_dibaca{background:#fff0f0;color:#dc2626;}
.status-dibaca{background:#fffbeb;color:#d97706;}
.status-didisposisi{background:#eff6ff;color:#2557a7;}
.status-selesai{background:#f0fdf4;color:#16a34a;}
.sifat-badge{padding:3px 10px;border-radius:6px;font-size:11px;font-weight:700;}
.sifat-segera{background:#fff0f0;color:#dc2626;}
.sifat-penting{background:#fffbeb;color:#d97706;}
.sifat-rahasia{background:#f5f3ff;color:#7c3aed;}
.sifat-biasa{background:#f1f5f9;color:#64748b;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;border:none;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s;}
.btn-primary{background:var(--primary);color:#fff;}
.btn-primary:hover{background:var(--primary-light);}
.btn-success{background:#16a34a;color:#fff;}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--text);}
.btn-outline:hover{border-color:var(--primary-light);color:var(--primary);}
.btn-sm{padding:5px 10px;font-size:12px;}
.file-preview { display:flex; align-items:center; gap:10px; padding:12px 16px; background:#f8fafc; border:1px solid var(--border); border-radius:8px; }
.file-preview i { font-size:24px; color:var(--primary-light); }
.file-preview .file-info span { font-size:12px; color:var(--text-muted); display:block; }
.disposisi-item { padding:12px 20px; border-bottom:1px solid #f1f5f9; }
.disposisi-item:last-child{border:none;}
.disposisi-meta { font-size:11px; color:var(--text-muted); margin-top:4px; }
.timeline { padding:0 20px; }
.timeline-item { display:flex; gap:12px; padding:12px 0; border-bottom:1px solid #f1f5f9; }
.timeline-item:last-child{border:none;}
.timeline-dot { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0; margin-top:2px; }
.tl-merah{background:#fff0f0;color:#dc2626;}
.tl-kuning{background:#fffbeb;color:#d97706;}
.tl-biru{background:#eff6ff;color:#2557a7;}
.tl-hijau{background:#f0fdf4;color:#16a34a;}
</style>

<!-- Action Bar -->
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
    <a href="{{ route('surat-masuk.index') }}" class="btn btn-outline btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
    <a href="{{ route('disposisi.create', ['surat_masuk_id'=>$suratMasuk->id]) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-diagram-3"></i> Buat Disposisi
    </a>
    <a href="{{ route('surat-masuk.edit', $suratMasuk->id) }}" class="btn btn-outline btn-sm">
        <i class="bi bi-pencil"></i> Edit
    </a>
    @if($suratMasuk->file_path)
    <a href="{{ Storage::url($suratMasuk->file_path) }}" target="_blank" class="btn btn-outline btn-sm">
        <i class="bi bi-file-earmark-arrow-down"></i> Unduh Scan
    </a>
    @endif
    <form method="POST" action="{{ route('surat-masuk.destroy', $suratMasuk->id) }}" style="display:inline;" onsubmit="return confirm('Hapus surat ini?')">
        @csrf @method('DELETE')
        <button class="btn btn-sm" style="background:#fff0f0;color:#dc2626;border:1px solid #fca5a5;">
            <i class="bi bi-trash3"></i> Hapus
        </button>
    </form>
</div>

<div class="detail-grid">
    <!-- Kolom Kiri: Detail Surat -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <!-- Info Utama -->
        <div class="detail-card">
            <div class="card-section-title"><i class="bi bi-envelope" style="margin-right:6px;"></i>Informasi Surat</div>
            <table class="detail-table">
                <tr><td>Nomor Surat</td><td><strong>{{ $suratMasuk->nomor_surat }}</strong></td></tr>
                <tr><td>Tanggal Surat</td><td>{{ $suratMasuk->tanggal_surat->format('d F Y') }}</td></tr>
                <tr><td>Tanggal Diterima</td><td>{{ $suratMasuk->tanggal_terima->format('d F Y') }}</td></tr>
                <tr><td>Pengirim</td><td>{{ $suratMasuk->pengirim }}</td></tr>
                <tr><td>Perihal</td><td>{{ $suratMasuk->perihal }}</td></tr>
                <tr><td>Sifat</td><td><span class="sifat-badge sifat-{{ $suratMasuk->sifat }}">{{ ucfirst($suratMasuk->sifat) }}</span></td></tr>
                <tr><td>Status</td><td><span class="status-badge status-{{ $suratMasuk->status }}">{{ $suratMasuk->status_label }}</span></td></tr>
                <tr><td>Unit Kerja</td><td>{{ $suratMasuk->unitKerja?->nama ?? '-' }}</td></tr>
                <tr><td>Diinput oleh</td><td>{{ $suratMasuk->creator?->display_name ?? '-' }}</td></tr>
                @if($suratMasuk->ringkasan)
                <tr><td>Ringkasan</td><td style="white-space:pre-line;">{{ $suratMasuk->ringkasan }}</td></tr>
                @endif
            </table>
        </div>

        <!-- File Lampiran -->
        @if($suratMasuk->file_path)
        <div class="detail-card">
            <div class="card-section-title"><i class="bi bi-paperclip" style="margin-right:6px;"></i>Dokumen Scan</div>
            <div style="padding:16px;">
                <div class="file-preview">
                    <i class="bi bi-file-earmark-pdf-fill" style="color:#dc2626;font-size:28px;"></i>
                    <div class="file-info">
                        <strong style="font-size:13px;">{{ basename($suratMasuk->file_path) }}</strong>
                        <span>Scan / Lampiran Surat</span>
                    </div>
                    <a href="{{ Storage::url($suratMasuk->file_path) }}" target="_blank" class="btn btn-outline btn-sm" style="margin-left:auto;">
                        <i class="bi bi-eye"></i> Buka
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Disposisi List -->
        @if($suratMasuk->disposisi->count() > 0)
        <div class="detail-card">
            <div class="card-section-title"><i class="bi bi-diagram-3" style="margin-right:6px;"></i>Riwayat Disposisi</div>
            @foreach($suratMasuk->disposisi as $disp)
            <div class="disposisi-item">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div style="font-size:13px;font-weight:600;">{{ $disp->pemberi?->display_name }}</div>
                        <div style="font-size:13px;color:var(--text);margin-top:4px;">{{ $disp->isi_disposisi }}</div>
                        <div class="disposisi-meta">
                            Kepada: {{ $disp->penerima->pluck('nama_lengkap')->implode(', ') }}
                            @if($disp->tanggal_deadline) · Deadline: {{ $disp->tanggal_deadline->format('d M Y') }} @endif
                        </div>
                    </div>
                    <span class="status-badge status-{{ $disp->status }}" style="font-size:11px;">{{ $disp->STATUS_LABELS[$disp->status] ?? $disp->status }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Kolom Kanan: Timeline & Aksi -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <!-- Timeline Status -->
        <div class="detail-card">
            <div class="card-section-title"><i class="bi bi-clock-history" style="margin-right:6px;"></i>Timeline Status</div>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot tl-merah"><i class="bi bi-inbox-fill"></i></div>
                    <div>
                        <div style="font-size:13px;font-weight:600;">Diterima</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $suratMasuk->created_at->format('d M Y, H:i') }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">oleh {{ $suratMasuk->creator?->display_name }}</div>
                    </div>
                </div>
                @if(in_array($suratMasuk->status, ['dibaca','didisposisi','selesai']))
                <div class="timeline-item">
                    <div class="timeline-dot tl-kuning"><i class="bi bi-eye-fill"></i></div>
                    <div>
                        <div style="font-size:13px;font-weight:600;">Sudah Dibaca</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $suratMasuk->updated_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
                @endif
                @if(in_array($suratMasuk->status, ['didisposisi','selesai']))
                <div class="timeline-item">
                    <div class="timeline-dot tl-biru"><i class="bi bi-diagram-3-fill"></i></div>
                    <div>
                        <div style="font-size:13px;font-weight:600;">Didisposisi</div>
                        <div style="font-size:11px;color:var(--text-muted);">Disposisi telah dikirim ke {{ $suratMasuk->disposisi->first()?->penerima->count() ?? 0 }} penerima</div>
                    </div>
                </div>
                @endif
                @if($suratMasuk->status === 'selesai')
                <div class="timeline-item">
                    <div class="timeline-dot tl-hijau"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <div style="font-size:13px;font-weight:600;">Selesai</div>
                        <div style="font-size:11px;color:var(--text-muted);">Semua disposisi telah ditangani</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Aksi Cepat -->
        <div class="detail-card">
            <div class="card-section-title"><i class="bi bi-lightning-fill" style="margin-right:6px;"></i>Aksi Cepat</div>
            <div style="padding:16px;display:flex;flex-direction:column;gap:8px;">
                <a href="{{ route('disposisi.create', ['surat_masuk_id'=>$suratMasuk->id]) }}" class="btn btn-primary" style="justify-content:center;">
                    <i class="bi bi-diagram-3"></i> Buat Disposisi
                </a>
                <a href="{{ route('surat-masuk.edit', $suratMasuk->id) }}" class="btn btn-outline" style="justify-content:center;">
                    <i class="bi bi-pencil"></i> Edit Data Surat
                </a>
                @if($suratMasuk->file_path)
                <a href="{{ Storage::url($suratMasuk->file_path) }}" target="_blank" class="btn btn-outline" style="justify-content:center;">
                    <i class="bi bi-file-earmark-arrow-down"></i> Unduh Dokumen
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
