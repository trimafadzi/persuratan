@extends('layouts.app')
@section('title','Tambah Role')
@section('page-title','Tambah Role Baru')
@section('page-subtitle','Buat hak akses baru untuk user')

@section('content')
<div style="max-width:600px;">
    <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow-sm);">
        <form method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Nama Role <span style="color:var(--accent);">*</span></label>
                <input type="text" name="nama_role" value="{{ old('nama_role') }}" required placeholder="Contoh: Manajer, Staf IT"
                    style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('nama_role')?'var(--accent)':'var(--border)' }};border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                @error('nama_role')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Slug <span style="color:var(--accent);">*</span></label>
                <input type="text" name="slug" value="{{ old('slug') }}" required placeholder="Contoh: manajer, staf_it (tanpa spasi)"
                    style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('slug')?'var(--accent)':'var(--border)' }};border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Slug digunakan oleh sistem untuk pengecekan hak akses. Gunakan huruf kecil dan garis bawah (_).</div>
                @error('slug')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Deskripsi (Opsional)</label>
                <textarea name="description" rows="3" placeholder="Tulis deskripsi singkat tentang hak akses ini..."
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;resize:vertical;">{{ old('description') }}</textarea>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;">
                    <i class="bi bi-check-lg"></i> Simpan Role
                </button>
                <a href="{{ route('admin.roles.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:#fff;border:1.5px solid var(--border);color:var(--text);font-size:14px;font-weight:600;text-decoration:none;">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
