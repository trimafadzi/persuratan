@extends('layouts.app')
@section('title','Unit Kerja')
@section('page-title','Unit Kerja')
@section('page-subtitle','Struktur organisasi RSU Universitas Kristen Indonesia')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div></div>
    <a href="{{ route('admin.unit-kerja.create') }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:var(--primary);color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        <i class="bi bi-plus-lg"></i> Tambah Unit Kerja
    </a>
</div>

<div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);">
    @forelse($units as $root)
        @include('admin.unit-kerja._unit-item', ['unit' => $root, 'depth' => 0])
    @empty
    <div style="padding:60px 20px;text-align:center;color:var(--text-muted);">
        <i class="bi bi-building" style="font-size:40px;display:block;margin-bottom:12px;opacity:.25;"></i>
        <p>Belum ada unit kerja.</p>
    </div>
    @endforelse
</div>
@endsection
