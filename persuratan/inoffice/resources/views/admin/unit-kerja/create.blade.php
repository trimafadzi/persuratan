@extends('layouts.app')
@section('title','Tambah Unit Kerja')
@section('page-title','Tambah Unit Kerja')
@section('page-subtitle','Buat struktur unit kerja baru')

@section('content')
<div style="max-width:600px;">
    <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow-sm);">
        <form method="POST" action="{{ route('admin.unit-kerja.store') }}">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Kode Unit <span style="color:var(--accent);">*</span></label>
                <input type="text" name="kode" value="{{ old('kode') }}" required placeholder="Contoh: DIR, IGD, UMUM"
                    style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('kode')?'var(--accent)':'var(--border)' }};border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                @error('kode')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Nama Unit Kerja <span style="color:var(--accent);">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Instalasi Gawat Darurat"
                    style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('nama')?'var(--accent)':'var(--border)' }};border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                @error('nama')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Induk / Parent Unit <span style="font-weight:400;color:var(--text-muted);">(Kosongkan jika level tertinggi)</span></label>
                <select name="parent_id" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;appearance:none;background:#fff;">
                    <option value="">-- Tidak Ada Induk (Level 1) --</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id')==$parent->id?'selected':'' }}>{{ str_repeat('  ', $parent->level-1) }}{{ $parent->nama }} (Lvl {{ $parent->level }})</option>
                    @endforeach
                </select>
                @error('parent_id')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;">
                    <i class="bi bi-check-lg"></i> Simpan
                </button>
                <a href="{{ route('admin.unit-kerja.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:#fff;border:1.5px solid var(--border);color:var(--text);font-size:14px;font-weight:600;text-decoration:none;">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
