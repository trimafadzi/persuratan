@extends('layouts.app')
@section('title', 'Audit Log')
@section('page-title', 'Audit Log')
@section('page-subtitle', 'Rekam jejak aktivitas pengguna sistem')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:12px;flex-wrap:wrap;">
    <form method="GET" action="{{ route('admin.log.index') }}" style="display:flex;gap:8px;flex:1;max-width:400px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aksi, user, IP..." 
               style="flex:1;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;outline:none;">
        <button type="submit" style="padding:9px 16px;background:var(--primary);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
            <i class="bi bi-search"></i> Cari
        </button>
        @if(request('search'))
            <a href="{{ route('admin.log.index') }}" style="padding:9px 12px;background:#f1f5f9;color:var(--text);border:1px solid var(--border);border-radius:8px;font-size:13px;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;">
                Reset
            </a>
        @endif
    </form>
</div>

<div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);margin-bottom:16px;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid var(--border);">
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Tanggal & Waktu</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Pengguna</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Aksi / Aktivitas</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">IP Address</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">User Agent</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:12px 16px;font-size:13px;color:var(--text-muted);white-space:nowrap;">
                    {{ $log->timestamp ? $log->timestamp->translatedFormat('d M Y H:i:s') : '-' }}
                </td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600;color:var(--primary);">
                    @if($log->user)
                        {{ $log->user->nama_lengkap }} <span style="font-size:11px;font-weight:400;color:var(--text-muted);">({{ $log->user->username }})</span>
                    @else
                        <span style="color:var(--accent);">System / Unknown</span>
                    @endif
                </td>
                <td style="padding:12px 16px;font-size:13px;font-family:monospace;color:var(--text);">
                    <span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#eff6ff;color:#1e40af;margin-bottom:4px;">
                        {{ $log->action }}
                    </span>
                    @if($log->detail)
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                            {{ is_array($log->detail) ? json_encode($log->detail) : $log->detail }}
                        </div>
                    @endif
                </td>
                <td style="padding:12px 16px;font-size:12px;font-family:monospace;color:var(--text-muted);">
                    {{ $log->ip_address ?? '-' }}
                </td>
                <td style="padding:12px 16px;font-size:11px;color:var(--text-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $log->user_agent }}">
                    {{ $log->user_agent ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:40px;text-align:center;color:var(--text-muted);font-size:13px;">
                    Belum ada log aktivitas terdaftar.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $logs->links() }}
</div>
@endsection
