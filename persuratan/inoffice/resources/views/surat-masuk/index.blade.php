@extends('layouts.app')
@section('title', 'Surat Masuk')
@section('page-title', 'Surat Masuk')
@section('page-subtitle', 'Daftar seluruh surat masuk')

@section('content')
<style>
.filter-bar { background:#fff; border:1px solid var(--border); border-radius:var(--radius); padding:16px 20px; margin-bottom:16px; display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
.filter-bar input, .filter-bar select { padding:8px 12px; border:1.5px solid var(--border); border-radius:8px; font-size:13px; font-family:inherit; outline:none; color:var(--text); }
.filter-bar input:focus, .filter-bar select:focus { border-color:var(--primary-light); }
.filter-bar .filter-group { display:flex; flex-direction:column; gap:4px; }
.filter-bar label { font-size:11px; font-weight:600; color:var(--text-muted); }
.btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:8px; border:none; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; transition:all .2s; }
.btn-primary { background:var(--primary); color:#fff; }
.btn-primary:hover { background:var(--primary-light); }
.btn-outline { background:#fff; border:1.5px solid var(--border); color:var(--text); }
.btn-outline:hover { border-color:var(--primary-light); color:var(--primary); }
.btn-sm { padding:5px 10px; font-size:12px; }
.btn-danger-sm { background:#fff0f0; color:var(--danger); border:1px solid #fca5a5; padding:5px 10px; border-radius:6px; font-size:12px; }
.table-card { background:#fff; border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; box-shadow:var(--shadow-sm); }
.table-header { display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-bottom:1px solid var(--border); }
table { width:100%; border-collapse:collapse; }
thead th { background:#f8fafc; padding:10px 16px; text-align:left; font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--border); }
tbody tr { border-bottom:1px solid #f1f5f9; transition:background .15s; }
tbody tr:hover { background:#f8fafc; }
tbody tr:last-child { border-bottom:none; }
td { padding:12px 16px; font-size:13px; vertical-align:middle; }
.status-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:100px; font-size:11px; font-weight:700; }
.status-belum_dibaca { background:#fff0f0; color:#dc2626; }
.status-dibaca       { background:#fffbeb; color:#d97706; }
.status-didisposisi  { background:#eff6ff; color:#2557a7; }
.status-selesai      { background:#f0fdf4; color:#16a34a; }
.sifat-badge { padding:2px 8px; border-radius:6px; font-size:10px; font-weight:700; }
.sifat-segera   { background:#fff0f0; color:#dc2626; }
.sifat-penting  { background:#fffbeb; color:#d97706; }
.sifat-rahasia  { background:#f5f3ff; color:#7c3aed; }
.sifat-biasa    { background:#f1f5f9; color:#64748b; }
.perihal-link { color:var(--primary); font-weight:600; text-decoration:none; }
.perihal-link:hover { text-decoration:underline; }
.empty-state { padding:60px 20px; text-align:center; color:var(--text-muted); }
.empty-state i { font-size:48px; display:block; margin-bottom:12px; opacity:.25; }
.pagination-wrap { display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid var(--border); font-size:13px; color:var(--text-muted); }
.page-links { display:flex; gap:4px; }
.page-links a, .page-links span { padding:5px 10px; border-radius:6px; border:1px solid var(--border); font-size:12px; color:var(--text); text-decoration:none; }
.page-links .active { background:var(--primary); color:#fff; border-color:var(--primary); }
</style>

<!-- Toolbar -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div style="display:flex;gap:8px;">
        <a href="?status=belum_dibaca" class="btn btn-outline btn-sm {{ request('status')=='belum_dibaca'?'':''; }}">
            <span style="width:8px;height:8px;background:#dc2626;border-radius:50%;display:inline-block;"></span> Belum Dibaca
        </a>
        <a href="?status=didisposisi" class="btn btn-outline btn-sm">
            <span style="width:8px;height:8px;background:#2557a7;border-radius:50%;display:inline-block;"></span> Didisposisi
        </a>
        <a href="{{ route('surat-masuk.index') }}" class="btn btn-outline btn-sm">Semua</a>
    </div>
    <a href="{{ route('surat-masuk.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Input Surat Masuk
    </a>
</div>

<!-- Filter Bar -->
<form method="GET" class="filter-bar">
    <div class="filter-group" style="flex:1;min-width:200px;">
        <label>Cari Surat</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor, pengirim, perihal...">
    </div>
    <div class="filter-group">
        <label>Status</label>
        <select name="status">
            <option value="">Semua Status</option>
            @foreach(['belum_dibaca'=>'Belum Dibaca','dibaca'=>'Dibaca','didisposisi'=>'Didisposisi','selesai'=>'Selesai'] as $val=>$label)
                <option value="{{ $val }}" {{ request('status')==$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label>Sifat</label>
        <select name="sifat">
            <option value="">Semua</option>
            @foreach(['biasa','penting','rahasia','segera'] as $s)
                <option value="{{ $s }}" {{ request('sifat')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label>Dari Tanggal</label>
        <input type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}">
    </div>
    <div class="filter-group">
        <label>Sampai</label>
        <input type="date" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}">
    </div>
    <div class="filter-group">
        <label>Per Hal</label>
        <select name="per_page">
            @foreach([10,25,50,100] as $n)
                <option value="{{ $n }}" {{ request('per_page',$n==25?25:0)==$n?'selected':'' }}>{{ $n }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
    <a href="{{ route('surat-masuk.index') }}" class="btn btn-outline"><i class="bi bi-x-lg"></i></a>
</form>

<!-- Table -->
<div class="table-card">
    <div class="table-header">
        <span style="font-size:13px;font-weight:600;">
            Total: <strong>{{ $suratList->total() }}</strong> surat
        </span>
    </div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Surat</th>
                <th>Pengirim</th>
                <th>Perihal</th>
                <th>Tgl Terima</th>
                <th>Sifat</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suratList as $idx => $surat)
            <tr>
                <td style="color:var(--text-muted);">{{ $suratList->firstItem() + $idx }}</td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $surat->nomor_surat }}</td>
                <td>{{ $surat->pengirim }}</td>
                <td>
                    <a href="{{ route('surat-masuk.show', $surat->id) }}" class="perihal-link">
                        {{ Str::limit($surat->perihal, 60) }}
                    </a>
                </td>
                <td style="white-space:nowrap;">{{ $surat->tanggal_terima->format('d M Y') }}</td>
                <td><span class="sifat-badge sifat-{{ $surat->sifat }}">{{ ucfirst($surat->sifat) }}</span></td>
                <td>
                    <span class="status-badge status-{{ $surat->status }}">
                        <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                        {{ $surat->status_label }}
                    </span>
                </td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <a href="{{ route('surat-masuk.show', $surat->id) }}" class="btn btn-outline btn-sm" title="Detail"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('disposisi.create', ['surat_masuk_id'=>$surat->id]) }}" class="btn btn-outline btn-sm" title="Disposisi"><i class="bi bi-diagram-3"></i></a>
                        <a href="{{ route('surat-masuk.edit', $surat->id) }}" class="btn btn-outline btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Tidak ada surat masuk ditemukan.</p>
                    <a href="{{ route('surat-masuk.create') }}" class="btn btn-primary" style="margin-top:12px;display:inline-flex;">
                        <i class="bi bi-plus-lg"></i> Input Surat Masuk Pertama
                    </a>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($suratList->hasPages())
    <div class="pagination-wrap">
        <span>Menampilkan {{ $suratList->firstItem() }}–{{ $suratList->lastItem() }} dari {{ $suratList->total() }}</span>
        <div class="page-links">{!! $suratList->links('pagination::simple-tailwind') !!}</div>
    </div>
    @endif
</div>
@endsection
