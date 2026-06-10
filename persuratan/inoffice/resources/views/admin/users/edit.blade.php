@extends('layouts.app')
@section('title','Edit User')
@section('page-title','Edit User')
@section('page-subtitle',$user->display_name)
@section('content')
<div style="max-width:700px;">
    <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow-sm);">
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap',$user->nama_lengkap) }}" required
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                    @error('nama_lengkap')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Username *</label>
                    <input type="text" name="username" value="{{ old('username',$user->username) }}" required
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                    @error('username')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Email *</label>
                <input type="email" name="email" value="{{ old('email',$user->email) }}" required
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                @error('email')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Password Baru <span style="font-weight:400;color:var(--text-muted);">(kosongkan jika tidak ganti)</span></label>
                    <input type="password" name="password" minlength="8"
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation"
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Jabatan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan',$user->jabatan) }}"
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Unit Kerja</label>
                    <select name="unit_kerja_id" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;appearance:none;background:#fff;">
                        <option value="">-- Pilih Unit Kerja --</option>
                        @foreach($unitList as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_kerja_id',$user->unit_kerja_id)==$unit->id?'selected':'' }}>{{ str_repeat('  ',$unit->level-1) }}{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#4a5568;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active',1) ? 'checked':'' }} style="accent-color:var(--primary-light);width:16px;height:16px;">
                    User Aktif (dapat login)
                </label>
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:8px;">Role *</label>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;">
                    @php $userRoleIds = $user->roles->pluck('id')->toArray(); @endphp
                    @foreach($roles as $role)
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:1.5px solid {{ in_array($role->id, old('role_ids',$userRoleIds)) ? 'var(--primary-light)':'var(--border)' }};border-radius:8px;cursor:pointer;background:{{ in_array($role->id, old('role_ids',$userRoleIds)) ? '#eff6ff':'#fff' }};">
                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" {{ in_array($role->id, old('role_ids',$userRoleIds)) ? 'checked':'' }}
                            style="accent-color:var(--primary-light);width:15px;height:15px;">
                        <div>
                            <div style="font-size:12px;font-weight:700;">{{ $role->nama_role }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;">
                    <i class="bi bi-check-lg"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.users.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:#fff;border:1.5px solid var(--border);color:var(--text);font-size:14px;font-weight:600;text-decoration:none;">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
