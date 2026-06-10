@extends('layouts.app')
@section('title', 'Input Surat Masuk')
@section('page-title', 'Input Surat Masuk')
@section('page-subtitle', 'Tambah data surat masuk baru')

@section('content')
<style>
.form-card { background:#fff; border:1px solid var(--border); border-radius:var(--radius); padding:28px; max-width:800px; box-shadow:var(--shadow-sm); }
.form-row { display:grid; gap:16px; margin-bottom:16px; }
.form-row.cols-2 { grid-template-columns:1fr 1fr; }
.form-row.cols-3 { grid-template-columns:1fr 1fr 1fr; }
.form-group label { display:block; font-size:13px; font-weight:600; color:#4a5568; margin-bottom:6px; }
.form-group label .required { color:var(--accent); margin-left:2px; }
.form-control { width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:8px; font-size:14px; font-family:inherit; color:var(--text); outline:none; transition:border-color .2s,box-shadow .2s; }
.form-control:focus { border-color:var(--primary-light); box-shadow:0 0 0 3px rgba(37,87,167,.08); }
.form-control.is-invalid { border-color:var(--accent); }
.invalid-feedback { font-size:12px; color:var(--accent); margin-top:4px; }
select.form-control { background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23718096' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; appearance:none; padding-right:36px; }
textarea.form-control { resize:vertical; min-height:100px; }
.upload-zone { border:2px dashed var(--border); border-radius:10px; padding:28px; text-align:center; cursor:pointer; transition:all .2s; background:#fafafa; }
.upload-zone:hover, .upload-zone.drag { border-color:var(--primary-light); background:#eff6ff; }
.upload-zone i { font-size:32px; color:var(--text-muted); display:block; margin-bottom:8px; }
.upload-zone p { font-size:13px; color:var(--text-muted); }
.upload-zone .file-name { font-size:13px; font-weight:600; color:var(--primary); margin-top:8px; }
.btn { display:inline-flex; align-items:center; gap:6px; padding:10px 20px; border-radius:8px; border:none; font-size:14px; font-weight:600; cursor:pointer; text-decoration:none; transition:all .2s; }
.btn-primary { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
.btn-primary:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(26,58,107,.3); }
.btn-outline { background:#fff; border:1.5px solid var(--border); color:var(--text); }
.section-divider { font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.8px; padding:16px 0 8px; border-top:1px solid var(--border); margin-top:8px; }
</style>

<div class="form-card">
    <form method="POST" action="{{ route('surat-masuk.store') }}" enctype="multipart/form-data" id="formSuratMasuk">
        @csrf

        <div class="section-divider" style="border-top:none;padding-top:0;">Informasi Surat</div>

        <div class="form-row cols-2">
            <div class="form-group">
                <label>Nomor Surat <span class="required">*</span></label>
                <input type="text" name="nomor_surat" class="form-control {{ $errors->has('nomor_surat')?'is-invalid':'' }}"
                    value="{{ old('nomor_surat') }}" placeholder="Contoh: 001/SK/ORG/VI/2026">
                @error('nomor_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Sifat Surat <span class="required">*</span></label>
                <select name="sifat" class="form-control {{ $errors->has('sifat')?'is-invalid':'' }}">
                    @foreach(['biasa'=>'Biasa','penting'=>'Penting','rahasia'=>'Rahasia','segera'=>'Segera'] as $val=>$label)
                        <option value="{{ $val }}" {{ old('sifat')==$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('sifat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row cols-2">
            <div class="form-group">
                <label>Tanggal Surat <span class="required">*</span></label>
                <input type="date" name="tanggal_surat" class="form-control {{ $errors->has('tanggal_surat')?'is-invalid':'' }}"
                    value="{{ old('tanggal_surat') }}">
                @error('tanggal_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Tanggal Diterima <span class="required">*</span></label>
                <input type="date" name="tanggal_terima" class="form-control {{ $errors->has('tanggal_terima')?'is-invalid':'' }}"
                    value="{{ old('tanggal_terima', date('Y-m-d')) }}">
                @error('tanggal_terima')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Pengirim / Asal Surat <span class="required">*</span></label>
                <input type="text" name="pengirim" class="form-control {{ $errors->has('pengirim')?'is-invalid':'' }}"
                    value="{{ old('pengirim') }}" placeholder="Nama instansi atau perorangan pengirim">
                @error('pengirim')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Perihal <span class="required">*</span></label>
                <input type="text" name="perihal" class="form-control {{ $errors->has('perihal')?'is-invalid':'' }}"
                    value="{{ old('perihal') }}" placeholder="Isi perihal surat secara singkat">
                @error('perihal')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row cols-2">
            <div class="form-group">
                <label>Ditujukan ke Unit Kerja</label>
                <select name="unit_kerja_id" class="form-control">
                    <option value="">-- Pilih Unit Kerja --</option>
                    @foreach($unitKerjaList as $unit)
                        <option value="{{ $unit->id }}" {{ old('unit_kerja_id')==$unit->id?'selected':'' }}>
                            {{ str_repeat('&nbsp;&nbsp;', $unit->level - 1) }}{{ $unit->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Ringkasan Isi Surat</label>
                <textarea name="ringkasan" class="form-control" placeholder="Tuliskan ringkasan isi surat (opsional)...">{{ old('ringkasan') }}</textarea>
            </div>
        </div>

        <div class="section-divider">Lampiran / Scan Surat</div>

        <div class="form-row">
            <div class="form-group">
                <label>Upload File (PDF / JPG / PNG, maks 75 MB)</label>
                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('file_scan').click()">
                    <i class="bi bi-cloud-upload"></i>
                    <p>Klik atau drag & drop file scan surat di sini</p>
                    <p style="font-size:11px;margin-top:4px;">PDF, JPG, PNG — Maks 75 MB</p>
                    <div class="file-name" id="fileName"></div>
                </div>
                <input type="file" id="file_scan" name="file_scan" accept=".pdf,.jpg,.jpeg,.png" style="display:none"
                    onchange="showFileName(this)">
                @error('file_scan')<div class="invalid-feedback" style="display:block;">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Simpan Surat Masuk
            </button>
            <a href="{{ route('surat-masuk.index') }}" class="btn btn-outline">
                <i class="bi bi-arrow-left"></i> Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function showFileName(input) {
    const el = document.getElementById('fileName');
    if (input.files && input.files[0]) {
        el.textContent = '✓ ' + input.files[0].name;
        document.getElementById('uploadZone').style.borderColor = 'var(--success-light)';
        document.getElementById('uploadZone').style.background = '#f0fdf4';
    }
}

// Drag and drop
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag'));
zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag');
    const file = e.dataTransfer.files[0];
    if (file) {
        const input = document.getElementById('file_scan');
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        showFileName(input);
    }
});
</script>
@endpush
@endsection
