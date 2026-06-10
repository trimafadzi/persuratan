@extends('layouts.app')
@section('title', 'Edit Surat Masuk')
@section('page-title', 'Edit Surat Masuk')
@section('page-subtitle', $suratMasuk->perihal)

@section('content')
<div style="max-width:800px;">
    <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow-sm);">
        <form method="POST" action="{{ route('surat-masuk.update', $suratMasuk->id) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Nomor Surat *</label>
                    <input type="text" name="nomor_surat" class="form-control {{ $errors->has('nomor_surat')?'is-invalid':'' }}"
                        value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}"
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                    @error('nomor_surat')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Sifat Surat *</label>
                    <select name="sifat" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;appearance:none;">
                        @foreach(['biasa'=>'Biasa','penting'=>'Penting','rahasia'=>'Rahasia','segera'=>'Segera'] as $val=>$label)
                            <option value="{{ $val }}" {{ old('sifat',$suratMasuk->sifat)==$val?'selected':'' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Tanggal Surat *</label>
                    <input type="date" name="tanggal_surat"
                        value="{{ old('tanggal_surat', $suratMasuk->tanggal_surat->format('Y-m-d')) }}"
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Tanggal Diterima *</label>
                    <input type="date" name="tanggal_terima"
                        value="{{ old('tanggal_terima', $suratMasuk->tanggal_terima->format('Y-m-d')) }}"
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Pengirim *</label>
                <input type="text" name="pengirim" value="{{ old('pengirim', $suratMasuk->pengirim) }}"
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Perihal *</label>
                <input type="text" name="perihal" value="{{ old('perihal', $suratMasuk->perihal) }}"
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Ringkasan</label>
                <textarea name="ringkasan" rows="3"
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;resize:vertical;">{{ old('ringkasan', $suratMasuk->ringkasan) }}</textarea>
            </div>

            @if($suratMasuk->file_path)
            <div style="margin-bottom:16px;padding:12px;background:#f8fafc;border:1px solid var(--border);border-radius:8px;font-size:13px;">
                <i class="bi bi-file-earmark-check" style="color:var(--success-light);"></i>
                File saat ini: <strong>{{ basename($suratMasuk->file_path) }}</strong>
                <span style="color:var(--text-muted);margin-left:8px;">Upload file baru untuk mengganti.</span>
            </div>
            @endif

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">
                    {{ $suratMasuk->file_path ? 'Ganti File Scan' : 'Upload File Scan' }} (Opsional)
                </label>
                <input type="file" name="file_scan" accept=".pdf,.jpg,.jpeg,.png"
                    style="width:100%;padding:10px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;">
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:var(--primary);color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;">
                    <i class="bi bi-check-lg"></i> Simpan Perubahan
                </button>
                <a href="{{ route('surat-masuk.show', $suratMasuk->id) }}"
                    style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:#fff;border:1.5px solid var(--border);color:var(--text);font-size:14px;font-weight:600;text-decoration:none;">
                    <i class="bi bi-x-lg"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
