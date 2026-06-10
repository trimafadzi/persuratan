@extends('layouts.app')
@section('title','Manajemen Role')
@section('page-title','Manajemen Role')
@section('page-subtitle','Kelola hak akses sistem')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div></div>
    <a href="{{ route('admin.roles.create') }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:var(--primary);color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        <i class="bi bi-shield-plus"></i> Tambah Role
    </a>
</div>

<div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid var(--border);">
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Nama Role</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Slug</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Deskripsi</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">User</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roles as $role)
            <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:12px 16px;font-size:13px;font-weight:700;color:var(--primary);">{{ $role->nama_role }}</td>
                <td style="padding:12px 16px;font-size:12px;font-family:monospace;color:var(--text-muted);">{{ $role->slug }}</td>
                <td style="padding:12px 16px;font-size:13px;">{{ $role->description ?? '-' }}</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600;">
                    <span style="display:inline-block;padding:2px 8px;background:#eff6ff;color:#2557a7;border-radius:100px;font-size:11px;">{{ $role->users_count }} user</span>
                </td>
                <td style="padding:12px 16px;">
                    <div style="display:flex;gap:4px;">
                        <a href="{{ route('admin.roles.edit', $role->id) }}" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:#fff;border:1px solid var(--border);border-radius:6px;font-size:12px;color:var(--text);text-decoration:none;font-weight:600;"><i class="bi bi-pencil"></i></a>
                        @if($role->users_count == 0 && !in_array($role->slug, ['superadmin','admin']))
                        <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}" onsubmit="return confirm('Hapus role ini?')" style="display:inline;">
                            @csrf @method('DELETE')
                            <button style="display:inline-flex;align-items:center;padding:5px 8px;background:#fff0f0;border:1px solid #fca5a5;border-radius:6px;font-size:12px;color:#dc2626;cursor:pointer;"><i class="bi bi-trash3"></i></button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:40px;text-align:center;">Data kosong</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
