@extends('layouts.app')
@section('title','Buat Surat Keluar')
@section('page-title','Buat Surat Keluar')
@section('page-subtitle','Nomor surat akan digenerate otomatis')

@section('content')
<div style="max-width:800px;">
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:10px;">
        <i class="bi bi-info-circle-fill" style="color:var(--primary-light);font-size:18px;"></i>
        <span>Nomor surat keluar akan <strong>digenerate otomatis</strong> sesuai format RSU UKI: <code style="background:#dbeafe;padding:2px 6px;border-radius:4px;">SK/RSU-UKI/{{ $user->unitKerja?->kode ?? 'RSU' }}/001/{{ now()->format('m') }}/{{ now()->year }}</code></span>
    </div>
    <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow-sm);">
        <form method="POST" action="{{ route('surat-keluar.store') }}" enctype="multipart/form-data">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Penerima / Ditujukan Ke <span style="color:var(--accent);">*</span></label>
                    <input type="text" name="penerima" value="{{ old('penerima') }}"
                        placeholder="Nama/Instansi penerima surat"
                        style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('penerima')?'var(--accent)':'var(--border)' }};border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                    @error('penerima')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Sifat Surat <span style="color:var(--accent);">*</span></label>
                    <select name="sifat" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;outline:none;appearance:none;background:#fff url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 fill=%22%23718096%22 viewBox=%220 0 16 16%22><path d=%22M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z%22/></svg>') no-repeat right 12px center;padding-right:36px;">
                        @foreach(['biasa'=>'Biasa','penting'=>'Penting','rahasia'=>'Rahasia','segera'=>'Segera'] as $v=>$l)
                            <option value="{{ $v }}" {{ old('sifat')==$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Perihal <span style="color:var(--accent);">*</span></label>
                <input type="text" name="perihal" value="{{ old('perihal') }}"
                    placeholder="Tulis perihal surat..."
                    style="width:100%;padding:10px 14px;border:1.5px solid {{ $errors->has('perihal')?'var(--accent)':'var(--border)' }};border-radius:8px;font-size:14px;font-family:inherit;outline:none;">
                @error('perihal')<div style="font-size:12px;color:var(--accent);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Isi / Ringkasan Surat</label>
                <textarea name="isi" rows="5" placeholder="Isi surat atau ringkasan singkat..."
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;resize:vertical;outline:none;">{{ old('isi') }}</textarea>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;">Upload File Surat (PDF/DOC, maks 75 MB)</label>
                <input type="file" name="file_surat" accept=".pdf,.doc,.docx"
                    style="width:100%;padding:10px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;">
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;">
                    <i class="bi bi-send-fill"></i> Buat Surat Keluar
                </button>
                <a href="{{ route('surat-keluar.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;background:#fff;border:1.5px solid var(--border);color:var(--text);font-size:14px;font-weight:600;text-decoration:none;">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
