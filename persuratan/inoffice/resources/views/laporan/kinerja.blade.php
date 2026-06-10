@extends('layouts.app')
@section('title','Laporan Kinerja')
@section('page-title','Laporan Kinerja Pegawai')
@section('page-subtitle','Penilaian volume & ketuntasan disposisi per periode')
@section('content')
<form method="GET" style="display:flex;gap:10px;align-items:flex-end;margin-bottom:16px;flex-wrap:wrap;">
    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:4px;">Periode</label>
        <input type="month" name="periode" value="{{ $periode }}"
            style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
    </div>
    <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--primary);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;"><i class="bi bi-funnel"></i> Filter</button>
</form>

<div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);background:#f8fafc;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:13px;font-weight:700;">Kinerja {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</span>
        <span style="font-size:11px;color:var(--text-muted);">Skor = 20% volume + 80% ketuntasan</span>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid var(--border);">
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">#</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Pegawai</th>
                <th style="padding:10px 16px;text-align:center;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Disposisi Kirim</th>
                <th style="padding:10px 16px;text-align:center;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Selesai / Total</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Ketuntasan</th>
                <th style="padding:10px 16px;text-align:center;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Skor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $idx => $data)
            @php $u = $data['user']; @endphp
            <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:12px 16px;font-size:13px;font-weight:700;color:var(--text-muted);">{{ $loop->iteration }}</td>
                <td style="padding:12px 16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;flex-shrink:0;">{{ $u->initials }}</div>
                        <div>
                            <div style="font-size:13px;font-weight:600;">{{ $u->display_name }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">{{ $u->unitKerja?->nama ?? $u->roles->first()?->nama_role }}</div>
                        </div>
                    </div>
                </td>
                <td style="padding:12px 16px;text-align:center;font-size:16px;font-weight:700;">{{ $data['volume'] }}</td>
                <td style="padding:12px 16px;text-align:center;font-size:13px;color:var(--text-muted);">{{ $data['selesai'] }} / {{ $data['total_disp'] }}</td>
                <td style="padding:12px 16px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="flex:1;height:8px;background:#f1f5f9;border-radius:100px;overflow:hidden;">
                            <div style="height:100%;width:{{ $data['ketuntasan'] }}%;background:{{ $data['ketuntasan']>=80?'#16a34a':($data['ketuntasan']>=50?'#d97706':'#dc2626') }};border-radius:100px;transition:width .6s;"></div>
                        </div>
                        <span style="font-size:12px;font-weight:700;min-width:35px;color:{{ $data['ketuntasan']>=80?'#16a34a':($data['ketuntasan']>=50?'#d97706':'#dc2626') }};">{{ $data['ketuntasan'] }}%</span>
                    </div>
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    <span style="display:inline-block;width:40px;height:40px;border-radius:50%;background:{{ $data['skor']>=80?'#f0fdf4':($data['skor']>=50?'#fffbeb':'#fff0f0') }};color:{{ $data['skor']>=80?'#16a34a':($data['skor']>=50?'#d97706':'#dc2626') }};font-size:14px;font-weight:800;line-height:40px;text-align:center;">
                        {{ $data['skor'] }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
