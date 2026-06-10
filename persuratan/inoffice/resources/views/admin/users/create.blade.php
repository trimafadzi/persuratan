@extends('layouts.app')
@section('title','Tambah User')
@section('page-title','Tambah User Baru')
@section('page-subtitle','Daftarkan akun pegawai ke sistem inOffice')
@section('content')
<div style="max-width:700px;">
    <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow-sm);">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Nama Lengkap <span style="color:var(--accent);">*</span></label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                        style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('nama_lengkap')?'var(--accent)':'var(--border)' }};border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                    @error('nama_lengkap')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Username <span style="color:var(--accent);">*</span></label>
                    <input type="text" name="username" value="{{ old('username') }}" required placeholder="huruf, angka, . _ -"
                        style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('username')?'var(--accent)':'var(--border)' }};border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                    @error('username')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Email <span style="color:var(--accent);">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="user@rsuuki.ac.id"
                    style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('email')?'var(--accent)':'var(--border)' }};border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                @error('email')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Password <span style="color:var(--accent);">*</span></label>
                    <input type="password" name="password" required minlength="8" placeholder="Min. 8 karakter"
                        style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('password')?'var(--accent)':'var(--border)' }};border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                    @error('password')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Konfirmasi Password <span style="color:var(--accent);">*</span></label>
                    <input type="password" name="password_confirmation" required placeholder="Ulangi password"
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Jabatan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan') }}" placeholder="Misal: Kepala Sub Bagian"
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Unit Kerja</label>
                    <select name="unit_kerja_id" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;appearance:none;background:#fff;">
                        <option value="">-- Pilih Unit Kerja --</option>
                        @foreach($unitList as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_kerja_id')==$unit->id?'selected':'' }}>{{ str_repeat('  ',$unit->level-1) }}{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:8px;">Role <span style="color:var(--accent);">*</span></label>
                @error('role_ids')<div style="font-size:12px;color:var(--accent);margin-bottom:6px;">{{ $message }}</div>@enderror
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;">
                    @foreach($roles as $role)
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:1.5px solid {{ in_array($role->id, old('role_ids',[])) ? 'var(--primary-light)':'var(--border)' }};border-radius:8px;cursor:pointer;background:{{ in_array($role->id, old('role_ids',[])) ? '#eff6ff':'#fff' }};">
                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" {{ in_array($role->id, old('role_ids',[])) ? 'checked':'' }}
                            style="accent-color:var(--primary-light);width:15px;height:15px;">
                        <div>
                            <div style="font-size:12px;font-weight:700;color:var(--text);">{{ $role->nama_role }}</div>
                            @if($role->description)<div style="font-size:10px;color:var(--text-muted);">{{ $role->description }}</div>@endif
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;">
                    <i class="bi bi-person-check-fill"></i> Simpan User
                </button>
                <a href="{{ route('admin.users.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:#fff;border:1.5px solid var(--border);color:var(--text);font-size:14px;font-weight:600;text-decoration:none;">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
