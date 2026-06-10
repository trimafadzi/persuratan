@extends('layouts.app')
@section('title','Laporan Surat Keluar')
@section('page-title','Laporan Surat Keluar')
@section('page-subtitle','Daftar rekapitulasi surat keluar')
@section('content')
<form method="GET" style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:14px 20px;margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;box-shadow:var(--shadow-sm);">
    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:4px;">Dari Tanggal</label>
        <input type="date" name="dari" value="{{ request('dari') }}" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
    </div>
    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:4px;">Sampai Tanggal</label>
        <input type="date" name="sampai" value="{{ request('sampai') }}" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
    </div>
    <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--primary);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;"><i class="bi bi-funnel"></i> Filter</button>
    <a href="{{ route('laporan.surat-keluar') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 12px;background:#fff;border:1.5px solid var(--border);color:var(--text);border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;"><i class="bi bi-arrow-clockwise"></i> Reset</a>
    
    <a href="{{ route('laporan.export', ['type'=>'surat-keluar', 'format'=>'csv']) }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#16a34a;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;margin-left:auto;"><i class="bi bi-filetype-csv"></i> Export CSV</a>
</form>

<div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid var(--border);">
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;">No. Surat</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Tgl Surat</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Penerima</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Perihal</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Sifat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $surat)
            <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:12px 16px;font-size:12px;font-weight:700;color:var(--primary);">{{ $surat->nomor_surat_otomatis }}</td>
                <td style="padding:12px 16px;font-size:12px;color:var(--text-muted);">{{ $surat->tanggal->format('d M Y') }}</td>
                <td style="padding:12px 16px;font-size:13px;">{{ $surat->penerima }}</td>
                <td style="padding:12px 16px;font-size:13px;"><a href="{{ route('surat-keluar.show',$surat->id) }}" style="color:var(--primary);text-decoration:none;">{{ Str::limit($surat->perihal,50) }}</a></td>
                <td style="padding:12px 16px;">
                    <span style="padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;background:{{ ['biasa'=>'#f1f5f9','penting'=>'#fffbeb','rahasia'=>'#f5f3ff','segera'=>'#fff0f0'][$surat->sifat] }};color:{{ ['biasa'=>'#64748b','penting'=>'#d97706','rahasia'=>'#7c3aed','segera'=>'#dc2626'][$surat->sifat] }};">{{ ucfirst($surat->sifat) }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:40px;text-align:center;">Data kosong</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($data->hasPages())
    <div style="padding:12px 20px;border-top:1px solid var(--border);">{{ $data->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
