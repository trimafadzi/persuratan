@extends('layouts.app')
@section('title','Detail Disposisi')
@section('page-title','Detail Disposisi')
@section('page-subtitle',Str::limit($disposisi->isi_disposisi,60))

@section('content')
<style>
.detail-grid{display:grid;grid-template-columns:1fr 320px;gap:16px;}
@media(max-width:900px){.detail-grid{grid-template-columns:1fr;}}
.card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);}
.card-head{font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;padding:12px 20px;border-bottom:1px solid var(--border);background:#f8fafc;}
.detail-row{display:flex;gap:12px;padding:11px 20px;border-bottom:1px solid #f1f5f9;font-size:13px;}
.detail-row:last-child{border:none;}
.dl{color:var(--text-muted);font-weight:600;min-width:130px;flex-shrink:0;}
.dd{color:var(--text);}
.status-chip{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:100px;font-size:11px;font-weight:700;}
.chip-pending{background:#fffbeb;color:#d97706;}
.chip-selesai{background:#f0fdf4;color:#16a34a;}
.chip-diteruskan{background:#eff6ff;color:#2557a7;}
.chip-dibatalkan{background:#f1f5f9;color:#64748b;}
.penerima-list{padding:12px 20px;display:flex;flex-wrap:wrap;gap:8px;}
.penerima-chip{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;}
.avatar-xs{width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;border:none;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s;}
.btn-primary{background:var(--primary);color:#fff;}
.btn-primary:hover{background:var(--primary-light);}
.btn-success{background:#16a34a;color:#fff;}
.btn-warning{background:#d97706;color:#fff;}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--text);}
.btn-sm{padding:5px 10px;font-size:12px;}
.laporan-item{padding:16px 20px;border-bottom:1px solid #f1f5f9;}
.laporan-item:last-child{border:none;}
.laporan-meta{font-size:11px;color:var(--text-muted);margin-top:6px;}
.tanggapan-box{background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:12px;margin-top:10px;font-size:13px;}
.tanggapan-box.rejected{background:#fff0f0;border-color:#fca5a5;}
.form-area{padding:0 20px 20px;}
.form-area textarea{width:100%;padding:10px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;resize:vertical;min-height:80px;outline:none;}
.form-area textarea:focus{border-color:var(--primary-light);}
.form-area input[type=file]{width:100%;padding:8px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;}
.anak-disposisi{padding:12px 20px;border-bottom:1px solid #f1f5f9;font-size:13px;}
.anak-disposisi:last-child{border:none;}
</style>

<!-- Actions Bar -->
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
    <a href="{{ route('disposisi.index') }}" class="btn btn-outline btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>

    @php $isPenerima = $disposisi->penerima->contains('id', auth()->id()); @endphp
    @php $isPemberi  = $disposisi->dari_user_id === auth()->id(); @endphp

    @if($isPenerima && $disposisi->status === 'pending')
    <button onclick="togglePanel('laporan')" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-check"></i> Kirim Laporan</button>
    <button onclick="togglePanel('teruskan')" class="btn btn-warning btn-sm" style="background:#d97706;color:#fff;"><i class="bi bi-diagram-3"></i> Teruskan</button>
    @endif

    @if($isPemberi && in_array($disposisi->status, ['pending','diteruskan']))
    <form method="POST" action="{{ route('disposisi.batal', $disposisi->id) }}" onsubmit="return confirm('Batalkan disposisi ini?')">
        @csrf @method('PATCH')
        <button class="btn btn-sm" style="background:#fff0f0;color:#dc2626;border:1px solid #fca5a5;"><i class="bi bi-x-circle"></i> Batalkan</button>
    </form>
    @endif
</div>

<div class="detail-grid">
    <!-- Kiri: Info + Laporan -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <!-- Info Disposisi -->
        <div class="card">
            <div class="card-head"><i class="bi bi-diagram-3" style="margin-right:6px;"></i>Informasi Disposisi</div>
            <div class="detail-row"><span class="dl">Isi Disposisi</span><span class="dd" style="white-space:pre-line;">{{ $disposisi->isi_disposisi }}</span></div>
            <div class="detail-row"><span class="dl">Surat Terkait</span><span class="dd"><a href="{{ route('surat-masuk.show',$disposisi->surat_masuk_id) }}" style="color:var(--primary);font-weight:600;">{{ $disposisi->suratMasuk?->perihal }}</a></span></div>
            <div class="detail-row"><span class="dl">Dari</span><span class="dd">{{ $disposisi->pemberi?->display_name }}</span></div>
            <div class="detail-row"><span class="dl">Status</span><span class="dd"><span class="status-chip chip-{{ $disposisi->status }}">{{ ['pending'=>'Menunggu','diteruskan'=>'Diteruskan','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'][$disposisi->status] }}</span></span></div>
            <div class="detail-row"><span class="dl">Tanggal</span><span class="dd">{{ $disposisi->created_at->format('d F Y, H:i') }}</span></div>
            @if($disposisi->tanggal_deadline)
            <div class="detail-row">
                <span class="dl">Deadline</span>
                <span class="dd {{ $disposisi->isOverdue() ? 'overdue' : '' }}" style="{{ $disposisi->isOverdue()?'color:#dc2626;font-weight:700;':'' }}">
                    {{ $disposisi->tanggal_deadline->format('d F Y') }}
                    @if($disposisi->isOverdue()) <i class="bi bi-exclamation-triangle-fill"></i> Terlambat! @endif
                </span>
            </div>
            @endif
            <div class="detail-row"><span class="dl">Penerima</span>
                <span class="dd">
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($disposisi->penerima as $p)
                        <span class="penerima-chip">
                            <span class="avatar-xs">{{ $p->initials }}</span>
                            {{ $p->display_name }}
                            @if($p->pivot->is_read) <i class="bi bi-check2-all" style="color:var(--primary-light);" title="Sudah dibaca"></i> @endif
                        </span>
                    @endforeach
                    </div>
                </span>
            </div>
        </div>

        <!-- Form Laporan (toggle) -->
        <div class="card" id="panel-laporan" style="display:none;">
            <div class="card-head"><i class="bi bi-file-earmark-check" style="margin-right:6px;"></i>Kirim Laporan Pelaksanaan</div>
            <form method="POST" action="{{ route('disposisi.laporan', $disposisi->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-area" style="padding-top:16px;">
                    <label style="font-size:13px;font-weight:600;color:#4a5568;display:block;margin-bottom:6px;">Isi Laporan *</label>
                    <textarea name="isi_laporan" required placeholder="Tuliskan laporan pelaksanaan tindak lanjut disposisi ini..."></textarea>
                    <label style="font-size:13px;font-weight:600;color:#4a5568;display:block;margin-bottom:6px;margin-top:12px;">File Bukti (Opsional)</label>
                    <input type="file" name="file_bukti[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <div style="margin-top:12px;display:flex;gap:8px;">
                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-send"></i> Kirim Laporan</button>
                        <button type="button" onclick="togglePanel('laporan')" class="btn btn-outline btn-sm"><i class="bi bi-x-lg"></i> Tutup</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Form Teruskan (toggle) -->
        <div class="card" id="panel-teruskan" style="display:none;">
            <div class="card-head"><i class="bi bi-diagram-3" style="margin-right:6px;"></i>Teruskan Disposisi</div>
            <form method="POST" action="{{ route('disposisi.teruskan', $disposisi->id) }}">
                @csrf
                <div class="form-area" style="padding-top:16px;">
                    <label style="font-size:13px;font-weight:600;color:#4a5568;display:block;margin-bottom:6px;">Instruksi Lanjutan *</label>
                    <textarea name="isi_disposisi" required placeholder="Tuliskan instruksi untuk penerima berikutnya..."></textarea>
                    <label style="font-size:13px;font-weight:600;color:#4a5568;display:block;margin-bottom:6px;margin-top:12px;">Penerima *</label>
                    <div style="display:flex;flex-direction:column;gap:6px;max-height:200px;overflow-y:auto;padding:4px;border:1.5px solid var(--border);border-radius:8px;background:#fafafa;">
                        @foreach(\App\Models\User::where('is_active',true)->where('id','!=',auth()->id())->orderBy('nama_lengkap')->get() as $u)
                        <label style="display:flex;align-items:center;gap:8px;padding:7px 10px;border:1px solid var(--border);border-radius:6px;cursor:pointer;background:#fff;">
                            <input type="checkbox" name="penerima_ids[]" value="{{ $u->id }}" style="accent-color:var(--primary-light);">
                            <span class="avatar-xs" style="width:22px;height:22px;font-size:10px;">{{ $u->initials }}</span>
                            <span style="font-size:12px;font-weight:600;">{{ $u->display_name }}</span>
                        </label>
                        @endforeach
                    </div>
                    <label style="font-size:13px;font-weight:600;color:#4a5568;display:block;margin-bottom:6px;margin-top:12px;">Deadline</label>
                    <input type="date" name="tanggal_deadline" min="{{ date('Y-m-d') }}" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
                    <div style="margin-top:12px;display:flex;gap:8px;">
                        <button type="submit" class="btn btn-warning btn-sm" style="background:#d97706;color:#fff;"><i class="bi bi-send"></i> Teruskan</button>
                        <button type="button" onclick="togglePanel('teruskan')" class="btn btn-outline btn-sm"><i class="bi bi-x-lg"></i> Tutup</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Laporan yang Sudah Masuk -->
        @if($disposisi->laporan->count() > 0)
        <div class="card">
            <div class="card-head"><i class="bi bi-file-text" style="margin-right:6px;"></i>Laporan Pelaksanaan ({{ $disposisi->laporan->count() }})</div>
            @foreach($disposisi->laporan as $laporan)
            <div class="laporan-item">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
                    <strong style="font-size:13px;">{{ $laporan->pelapor?->display_name }}</strong>
                    <span class="status-chip {{ $laporan->status_tanggapan === 'approved' ? 'chip-selesai' : ($laporan->status_tanggapan === 'rejected' ? '' : 'chip-pending') }}"
                          style="{{ $laporan->status_tanggapan === 'rejected' ? 'background:#fff0f0;color:#dc2626;' : '' }}">
                        {{ $laporan->status_tanggapan ? ucfirst($laporan->status_tanggapan) : 'Menunggu Tanggapan' }}
                    </span>
                </div>
                <p style="font-size:13px;white-space:pre-line;">{{ $laporan->isi_laporan }}</p>
                @if($laporan->fileBukti->count() > 0)
                <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;">
                    @foreach($laporan->fileBukti as $f)
                    <a href="{{ Storage::url($f->file_path) }}" target="_blank" style="display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border:1px solid var(--border);border-radius:6px;font-size:11px;color:var(--primary);text-decoration:none;">
                        <i class="bi bi-paperclip"></i> {{ $f->file_name }}
                    </a>
                    @endforeach
                </div>
                @endif
                <div class="laporan-meta">{{ $laporan->created_at->format('d M Y, H:i') }}</div>

                @if($laporan->tanggapan)
                <div class="tanggapan-box {{ $laporan->status_tanggapan === 'rejected' ? 'rejected' : '' }}">
                    <strong style="font-size:12px;">Tanggapan {{ $laporan->penanggap?->display_name }}:</strong>
                    <p style="margin-top:4px;font-size:13px;">{{ $laporan->tanggapan }}</p>
                </div>
                @endif

                @if($isPemberi && !$laporan->status_tanggapan && $laporan->status === 'terkirim')
                <form method="POST" action="{{ route('disposisi.tanggapi', $disposisi->id) }}" style="margin-top:12px;background:#f8fafc;padding:12px;border-radius:8px;">
                    @csrf
                    <textarea name="tanggapan" required placeholder="Tulis tanggapan..." rows="2"
                        style="width:100%;padding:8px;border:1.5px solid var(--border);border-radius:6px;font-size:13px;font-family:inherit;resize:none;outline:none;"></textarea>
                    <div style="display:flex;gap:6px;margin-top:8px;">
                        <button name="status_tanggapan" value="approved" class="btn btn-success btn-sm"><i class="bi bi-check2-circle"></i> Setujui</button>
                        <button name="status_tanggapan" value="rejected" class="btn btn-sm" style="background:#fff0f0;color:#dc2626;border:1px solid #fca5a5;display:inline-flex;align-items:center;gap:6px;">
                            <i class="bi bi-x-circle"></i> Tolak / Revisi
                        </button>
                    </div>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Disposisi Anak -->
        @if($disposisi->children->count() > 0)
        <div class="card">
            <div class="card-head"><i class="bi bi-diagram-3" style="margin-right:6px;"></i>Disposisi Diteruskan ({{ $disposisi->children->count() }})</div>
            @foreach($disposisi->children as $child)
            <div class="anak-disposisi">
                <div style="display:flex;justify-content:space-between;">
                    <a href="{{ route('disposisi.show',$child->id) }}" style="color:var(--primary);font-weight:600;text-decoration:none;">{{ Str::limit($child->isi_disposisi,60) }}</a>
                    <span class="status-chip chip-{{ $child->status }}">{{ ['pending'=>'Menunggu','diteruskan'=>'Diteruskan','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'][$child->status] }}</span>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                    Ke: {{ $child->penerima->pluck('nama_lengkap')->implode(', ') }} · {{ $child->created_at->format('d M Y') }}
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Kanan: Info singkat -->
    <div>
        <div class="card">
            <div class="card-head"><i class="bi bi-info-circle" style="margin-right:6px;"></i>Ringkasan</div>
            <div style="padding:16px;font-size:13px;display:flex;flex-direction:column;gap:10px;">
                <div><span style="color:var(--text-muted);font-size:11px;font-weight:600;display:block;">STATUS</span>
                    <span class="status-chip chip-{{ $disposisi->status }}" style="margin-top:4px;">{{ ['pending'=>'Menunggu','diteruskan'=>'Diteruskan','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'][$disposisi->status] }}</span>
                </div>
                <div><span style="color:var(--text-muted);font-size:11px;font-weight:600;display:block;">DIBUAT</span>
                    {{ $disposisi->created_at->format('d M Y, H:i') }}
                </div>
                @if($disposisi->tanggal_deadline)
                <div><span style="color:var(--text-muted);font-size:11px;font-weight:600;display:block;">DEADLINE</span>
                    <span style="{{ $disposisi->isOverdue()?'color:#dc2626;font-weight:700;':'' }}">
                        {{ $disposisi->tanggal_deadline->format('d M Y') }}
                        @if($disposisi->isOverdue()) <br><small>⚠️ Sudah melewati deadline</small> @endif
                    </span>
                </div>
                @endif
                <div><span style="color:var(--text-muted);font-size:11px;font-weight:600;display:block;">LAPORAN</span>
                    {{ $disposisi->laporan->count() }} laporan diterima
                </div>
                <div><span style="color:var(--text-muted);font-size:11px;font-weight:600;display:block;">DITERUSKAN</span>
                    {{ $disposisi->children->count() }} kali
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePanel(name) {
    const el = document.getElementById('panel-' + name);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
    if(el.style.display !== 'none') el.scrollIntoView({behavior:'smooth',block:'start'});
}
</script>
@endpush
@endsection
