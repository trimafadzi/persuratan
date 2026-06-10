@extends('layouts.app')
@section('title','Detail Surat Keluar')
@section('page-title','Detail Surat Keluar')
@section('page-subtitle',$suratKeluar->nomor_surat_otomatis)
@section('content')
<div style="display:flex;gap:8px;margin-bottom:16px;">
    <a href="{{ route('surat-keluar.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:#fff;border:1.5px solid var(--border);color:var(--text);font-size:13px;font-weight:600;text-decoration:none;"><i class="bi bi-arrow-left"></i> Kembali</a>
    @if($suratKeluar->file_path)
    <a href="{{ Storage::url($suratKeluar->file_path) }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:#fff;border:1.5px solid var(--border);color:var(--text);font-size:13px;font-weight:600;text-decoration:none;"><i class="bi bi-download"></i> Unduh</a>
    @endif
    <form method="POST" action="{{ route('surat-keluar.destroy',$suratKeluar->id) }}" onsubmit="return confirm('Hapus surat keluar ini?')" style="display:inline;">
        @csrf @method('DELETE')
        <button style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:#fff0f0;border:1px solid #fca5a5;color:#dc2626;font-size:13px;font-weight:600;cursor:pointer;"><i class="bi bi-trash3"></i> Hapus</button>
    </form>
</div>
<div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;max-width:700px;box-shadow:var(--shadow-sm);">
    <div style="background:linear-gradient(135deg,var(--primary),var(--primary-light));padding:20px 24px;color:#fff;">
        <div style="font-size:11px;font-weight:700;opacity:.7;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;">Nomor Surat</div>
        <div style="font-size:22px;font-weight:800;">{{ $suratKeluar->nomor_surat_otomatis }}</div>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        @php $rows = [['Tanggal',$suratKeluar->tanggal->format('d F Y')],['Penerima',$suratKeluar->penerima],['Perihal',$suratKeluar->perihal],['Sifat',ucfirst($suratKeluar->sifat)],['Dibuat Oleh',$suratKeluar->creator?->display_name ?? '-']]; @endphp
        @foreach($rows as [$label,$value])
        <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:12px 20px;font-size:13px;font-weight:600;color:var(--text-muted);width:140px;">{{ $label }}</td>
            <td style="padding:12px 20px;font-size:13px;color:var(--text);">{{ $value }}</td>
        </tr>
        @endforeach
        @if($suratKeluar->isi)
        <tr><td style="padding:12px 20px;font-size:13px;font-weight:600;color:var(--text-muted);vertical-align:top;">Isi</td>
            <td style="padding:12px 20px;font-size:13px;white-space:pre-line;">{{ $suratKeluar->isi }}</td></tr>
        @endif
    </table>
</div>
@endsection
