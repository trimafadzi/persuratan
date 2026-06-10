@extends('layouts.app')
@section('title','Surat Keluar')
@section('page-title','Surat Keluar')
@section('page-subtitle','Daftar surat keluar dengan penomoran otomatis')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div style="font-size:13px;color:var(--text-muted);">Total: <strong style="color:var(--text);">{{ $suratList->total() }}</strong> surat keluar</div>
    <a href="{{ route('surat-keluar.create') }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:var(--primary);color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:all .2s;">
        <i class="bi bi-plus-lg"></i> Buat Surat Keluar
    </a>
</div>

<!-- Filter -->
<form method="GET" style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:14px 20px;margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;box-shadow:var(--shadow-sm);">
    <div style="flex:1;min-width:200px;">
        <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:4px;">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor, penerima, perihal..."
            style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
    </div>
    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:4px;">Sifat</label>
        <select name="sifat" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;appearance:none;padding-right:28px;">
            <option value="">Semua</option>
            @foreach(['biasa','penting','rahasia','segera'] as $s)
                <option value="{{ $s }}" {{ request('sifat')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--primary);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
        <i class="bi bi-search"></i> Cari
    </button>
    <a href="{{ route('surat-keluar.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 12px;background:#fff;border:1.5px solid var(--border);color:var(--text);border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        <i class="bi bi-x-lg"></i>
    </a>
</form>

<!-- Table -->
<div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid var(--border);">
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Nomor Surat</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Penerima</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Perihal</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Tanggal</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Sifat</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suratList as $surat)
            <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:12px 16px;font-size:12px;font-weight:700;color:var(--primary);">{{ $surat->nomor_surat_otomatis }}</td>
                <td style="padding:12px 16px;font-size:13px;">{{ $surat->penerima }}</td>
                <td style="padding:12px 16px;font-size:13px;">
                    <a href="{{ route('surat-keluar.show', $surat->id) }}" style="color:var(--primary);font-weight:600;text-decoration:none;">{{ Str::limit($surat->perihal,60) }}</a>
                </td>
                <td style="padding:12px 16px;font-size:12px;color:var(--text-muted);white-space:nowrap;">{{ $surat->tanggal->format('d M Y') }}</td>
                <td style="padding:12px 16px;">
                    <span style="padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;background:{{ ['biasa'=>'#f1f5f9','penting'=>'#fffbeb','rahasia'=>'#f5f3ff','segera'=>'#fff0f0'][$surat->sifat]??'#f1f5f9' }};color:{{ ['biasa'=>'#64748b','penting'=>'#d97706','rahasia'=>'#7c3aed','segera'=>'#dc2626'][$surat->sifat]??'#64748b' }};">
                        {{ ucfirst($surat->sifat) }}
                    </span>
                </td>
                <td style="padding:12px 16px;">
                    <div style="display:flex;gap:4px;">
                        <a href="{{ route('surat-keluar.show',$surat->id) }}" style="display:inline-flex;align-items:center;gap:4px;padding:5px 8px;background:#fff;border:1px solid var(--border);border-radius:6px;font-size:12px;color:var(--text);text-decoration:none;font-weight:600;"><i class="bi bi-eye"></i></a>
                        @if($surat->file_path)
                        <a href="{{ Storage::url($surat->file_path) }}" target="_blank" style="display:inline-flex;align-items:center;gap:4px;padding:5px 8px;background:#fff;border:1px solid var(--border);border-radius:6px;font-size:12px;color:var(--text);text-decoration:none;font-weight:600;"><i class="bi bi-download"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="padding:60px 20px;text-align:center;color:var(--text-muted);">
                <i class="bi bi-send" style="font-size:40px;display:block;margin-bottom:12px;opacity:.25;"></i>
                <p>Belum ada surat keluar.</p>
                <a href="{{ route('surat-keluar.create') }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:var(--primary);color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;margin-top:12px;">
                    <i class="bi bi-plus-lg"></i> Buat Surat Keluar
                </a>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($suratList->hasPages())
    <div style="padding:12px 20px;border-top:1px solid var(--border);font-size:13px;color:var(--text-muted);display:flex;justify-content:space-between;align-items:center;">
        <span>{{ $suratList->firstItem() }}–{{ $suratList->lastItem() }} dari {{ $suratList->total() }}</span>
        {{ $suratList->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
