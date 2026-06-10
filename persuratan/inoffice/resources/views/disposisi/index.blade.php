@extends('layouts.app')
@section('title','Disposisi')
@section('page-title','Disposisi')
@section('page-subtitle','Daftar disposisi masuk dan keluar')

@section('content')
<style>
.tab-nav{display:flex;gap:0;background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:16px;box-shadow:var(--shadow-sm);}
.tab-item{flex:1;text-align:center;padding:13px;font-size:13px;font-weight:600;color:var(--text-muted);text-decoration:none;border-bottom:3px solid transparent;transition:all .2s;}
.tab-item:hover{color:var(--primary);background:#f8fafc;}
.tab-item.active{color:var(--primary);border-bottom-color:var(--primary);background:linear-gradient(to bottom,#eff6ff,#fff);}
.disp-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);}
.disp-item{display:flex;gap:14px;padding:16px 20px;border-bottom:1px solid #f1f5f9;transition:background .15s;cursor:pointer;}
.disp-item:hover{background:#f8fafc;}
.disp-item:last-child{border:none;}
.disp-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.icon-pending{background:#fffbeb;color:#d97706;}
.icon-selesai{background:#f0fdf4;color:#16a34a;}
.icon-diteruskan{background:#eff6ff;color:#2557a7;}
.icon-dibatalkan{background:#f1f5f9;color:#64748b;}
.disp-body{flex:1;min-width:0;}
.disp-body h4{font-size:13px;font-weight:600;color:var(--text);margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.disp-body p{font-size:12px;color:var(--text-muted);}
.disp-meta{display:flex;align-items:center;gap:8px;margin-top:4px;flex-wrap:wrap;}
.status-chip{padding:2px 8px;border-radius:100px;font-size:10px;font-weight:700;}
.chip-pending{background:#fffbeb;color:#d97706;}
.chip-selesai{background:#f0fdf4;color:#16a34a;}
.chip-diteruskan{background:#eff6ff;color:#2557a7;}
.chip-dibatalkan{background:#f1f5f9;color:#64748b;}
.overdue{background:#fff0f0;color:#dc2626;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;border:none;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s;}
.btn-primary{background:var(--primary);color:#fff;}
.btn-primary:hover{background:var(--primary-light);}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--text);}
.btn-outline:hover{border-color:var(--primary-light);color:var(--primary);}
.btn-sm{padding:5px 10px;font-size:12px;}
.empty-state{padding:60px 20px;text-align:center;color:var(--text-muted);}
.empty-state i{font-size:48px;display:block;margin-bottom:12px;opacity:.25;}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div></div>
    <a href="{{ route('disposisi.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Buat Disposisi
    </a>
</div>

<!-- Tab Nav -->
<div class="tab-nav">
    <a href="?tab=masuk" class="tab-item {{ $tab=='masuk'?'active':'' }}">
        <i class="bi bi-inbox-fill" style="margin-right:6px;"></i> Disposisi Masuk
        @if($jumlahDisposisiPending > 0)
            <span style="background:var(--accent);color:#fff;font-size:10px;padding:2px 6px;border-radius:100px;margin-left:4px;">{{ $jumlahDisposisiPending }}</span>
        @endif
    </a>
    <a href="?tab=keluar" class="tab-item {{ $tab=='keluar'?'active':'' }}">
        <i class="bi bi-send-fill" style="margin-right:6px;"></i> Disposisi Keluar
    </a>
</div>

<!-- Filter -->
<form method="GET" style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari isi disposisi..."
        style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;flex:1;min-width:200px;font-family:inherit;outline:none;">
    <select name="status" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
        <option value="">Semua Status</option>
        @foreach(['pending'=>'Menunggu','diteruskan'=>'Diteruskan','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'] as $v=>$l)
            <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-outline btn-sm"><i class="bi bi-search"></i> Cari</button>
</form>

<!-- List -->
<div class="disp-card">
    @forelse($disposisiList as $d)
    <a href="{{ route('disposisi.show', $d->id) }}" class="disp-item" style="text-decoration:none;">
        <div class="disp-icon icon-{{ $d->status }}">
            <i class="bi bi-diagram-3-fill"></i>
        </div>
        <div class="disp-body">
            <h4>{{ Str::limit($d->isi_disposisi, 80) }}</h4>
            <p>Surat: {{ $d->suratMasuk?->perihal }}</p>
            <div class="disp-meta">
                @if($tab === 'masuk')
                    <span style="font-size:11px;color:var(--text-muted);">Dari: {{ $d->pemberi?->display_name }}</span>
                @else
                    <span style="font-size:11px;color:var(--text-muted);">Ke: {{ $d->penerima->pluck('nama_lengkap')->take(2)->implode(', ') }}{{ $d->penerima->count() > 2 ? ' +'.($d->penerima->count()-2) : '' }}</span>
                @endif
                <span class="status-chip chip-{{ $d->status }}">{{ ['pending'=>'Menunggu','diteruskan'=>'Diteruskan','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'][$d->status] ?? $d->status }}</span>
                @if($d->tanggal_deadline)
                    <span class="status-chip {{ $d->isOverdue() ? 'overdue' : '' }}" style="{{ $d->isOverdue()?'':'background:#f1f5f9;color:#64748b;' }}">
                        <i class="bi bi-alarm"></i> {{ $d->tanggal_deadline->format('d M Y') }}{{ $d->isOverdue() ? ' ⚠️' : '' }}
                    </span>
                @endif
                <span style="font-size:11px;color:var(--text-muted);margin-left:auto;">{{ $d->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </a>
    @empty
    <div class="empty-state">
        <i class="bi bi-diagram-3"></i>
        <p>Belum ada disposisi {{ $tab === 'masuk' ? 'masuk' : 'keluar' }}.</p>
        @if($tab === 'keluar')
        <a href="{{ route('disposisi.create') }}" class="btn btn-primary" style="display:inline-flex;margin-top:12px;">
            <i class="bi bi-plus-lg"></i> Buat Disposisi
        </a>
        @endif
    </div>
    @endforelse
</div>

@if($disposisiList->hasPages())
<div style="display:flex;justify-content:center;margin-top:16px;">
    {{ $disposisiList->appends(request()->query())->links() }}
</div>
@endif
@endsection
