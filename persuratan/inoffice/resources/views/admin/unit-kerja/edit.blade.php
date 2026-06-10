@extends('layouts.app')
@section('title','Edit Unit Kerja')
@section('page-title','Edit Unit Kerja')
@section('page-subtitle',$unitKerja->nama)

@section('content')
<div style="max-width:600px;">
    <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow-sm);">
        <form method="POST" action="{{ route('admin.unit-kerja.update', $unitKerja->id) }}">
            @csrf @method('PUT')
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Kode Unit *</label>
                <input type="text" name="kode" value="{{ old('kode', $unitKerja->kode) }}" required
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                @error('kode')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Nama Unit Kerja *</label>
                <input type="text" name="nama" value="{{ old('nama', $unitKerja->nama) }}" required
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                @error('nama')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Induk / Parent Unit</label>
                <select name="parent_id" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;appearance:none;background:#fff;">
                    <option value="">-- Tidak Ada Induk (Level 1) --</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $unitKerja->parent_id)==$parent->id?'selected':'' }}>{{ str_repeat('  ', $parent->level-1) }}{{ $parent->nama }} (Lvl {{ $parent->level }})</option>
                    @endforeach
                </select>
                @error('parent_id')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#4a5568;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $unitKerja->is_active) ? 'checked':'' }} style="accent-color:var(--primary-light);width:16px;height:16px;">
                    Unit Kerja Aktif
                </label>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;">
                    <i class="bi bi-check-lg"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.unit-kerja.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:#fff;border:1.5px solid var(--border);color:var(--text);font-size:14px;font-weight:600;text-decoration:none;">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
