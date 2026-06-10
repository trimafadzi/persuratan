@extends('layouts.app')
@section('title','Manajemen User')
@section('page-title','Manajemen User')
@section('page-subtitle','Kelola akun user dan hak akses sistem')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div style="font-size:13px;color:var(--text-muted);">Total: <strong style="color:var(--text);">{{ $users->total() }}</strong> user</div>
    <a href="{{ route('admin.users.create') }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:var(--primary);color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        <i class="bi bi-person-plus-fill"></i> Tambah User
    </a>
</div>

<!-- Filter -->
<form method="GET" style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:14px 20px;margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;box-shadow:var(--shadow-sm);">
    <div style="flex:1;min-width:180px;">
        <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:4px;">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, username, email..."
            style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
    </div>
    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:4px;">Role</label>
        <select name="role" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
            <option value="">Semua Role</option>
            @foreach($roles as $role)
                <option value="{{ $role->slug }}" {{ request('role')==$role->slug?'selected':'' }}>{{ $role->nama_role }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:4px;">Status</label>
        <select name="status" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
            <option value="">Semua</option>
            <option value="aktif" {{ request('status')=='aktif'?'selected':'' }}>Aktif</option>
            <option value="nonaktif" {{ request('status')=='nonaktif'?'selected':'' }}>Nonaktif</option>
        </select>
    </div>
    <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--primary);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;"><i class="bi bi-search"></i> Cari</button>
    <a href="{{ route('admin.users.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 12px;background:#fff;border:1.5px solid var(--border);color:var(--text);border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;"><i class="bi bi-x-lg"></i></a>
</form>

<!-- Table -->
<div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid var(--border);">
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">User</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Username / Email</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Role</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Unit Kerja</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Status</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:12px 16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;">{{ $user->initials }}</div>
                        <div>
                            <div style="font-size:13px;font-weight:600;color:var(--text);">{{ $user->display_name }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">{{ $user->jabatan ?? 'Tidak ada jabatan' }}</div>
                        </div>
                    </div>
                </td>
                <td style="padding:12px 16px;">
                    <div style="font-size:13px;font-weight:600;">{{ $user->username }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $user->email }}</div>
                </td>
                <td style="padding:12px 16px;">
                    <div style="display:flex;flex-wrap:wrap;gap:4px;">
                    @foreach($user->roles as $role)
                        <span style="padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;background:#eff6ff;color:#2557a7;">{{ $role->nama_role }}</span>
                    @endforeach
                    </div>
                </td>
                <td style="padding:12px 16px;font-size:13px;color:var(--text-muted);">{{ $user->unitKerja?->nama ?? '-' }}</td>
                <td style="padding:12px 16px;">
                    @if($user->is_active)
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:100px;font-size:11px;font-weight:700;background:#f0fdf4;color:#16a34a;">
                            <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span> Aktif
                        </span>
                    @else
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:100px;font-size:11px;font-weight:700;background:#f1f5f9;color:#64748b;">
                            <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span> Nonaktif
                        </span>
                    @endif
                </td>
                <td style="padding:12px 16px;">
                    <div style="display:flex;gap:4px;">
                        <a href="{{ route('admin.users.edit', $user->id) }}" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:#fff;border:1px solid var(--border);border-radius:6px;font-size:12px;color:var(--text);text-decoration:none;font-weight:600;transition:all .2s;" title="Edit"><i class="bi bi-pencil"></i></a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Nonaktifkan user {{ $user->username }}?')">
                            @csrf @method('DELETE')
                            <button style="display:inline-flex;align-items:center;padding:5px 8px;background:#fff0f0;border:1px solid #fca5a5;border-radius:6px;font-size:12px;color:#dc2626;cursor:pointer;" title="{{ $user->is_active ? 'Nonaktifkan' : 'Sudah nonaktif' }}" {{ !$user->is_active ? 'disabled' : '' }}>
                                <i class="bi bi-person-x-fill"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="padding:60px 20px;text-align:center;color:var(--text-muted);">
                <i class="bi bi-people" style="font-size:40px;display:block;margin-bottom:12px;opacity:.25;"></i>
                <p>Tidak ada user ditemukan.</p>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
    <div style="padding:12px 20px;border-top:1px solid var(--border);font-size:13px;color:var(--text-muted);">
        {{ $users->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
