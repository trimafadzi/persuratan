@extends('layouts.app')
@section('title','Buat Disposisi')
@section('page-title','Buat Disposisi')
@section('page-subtitle','Kirim perintah tindak lanjut ke bawahan')

@section('content')
<style>
.form-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:28px;max-width:800px;box-shadow:var(--shadow-sm);}
.form-group{margin-bottom:18px;}
.form-group label{display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;}
.req{color:var(--accent);}
.form-control{width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;color:var(--text);outline:none;transition:border-color .2s;}
.form-control:focus{border-color:var(--primary-light);box-shadow:0 0 0 3px rgba(37,87,167,.08);}
.form-control.err{border-color:var(--accent);}
.err-msg{font-size:12px;color:var(--accent);margin-top:4px;}
.surat-preview{background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:12px 16px;margin-top:8px;font-size:13px;}
.surat-preview strong{display:block;margin-bottom:4px;}
.user-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;max-height:300px;overflow-y:auto;padding:8px;border:1.5px solid var(--border);border-radius:8px;background:#fafafa;}
.user-check{display:flex;align-items:center;gap:8px;padding:8px 10px;border:1px solid var(--border);border-radius:8px;cursor:pointer;transition:all .2s;background:#fff;}
.user-check:hover{border-color:var(--primary-light);background:#eff6ff;}
.user-check input[type=checkbox]{accent-color:var(--primary-light);width:15px;height:15px;flex-shrink:0;}
.user-check input:checked~.user-label{color:var(--primary);}
.user-check.selected{border-color:var(--primary-light);background:#eff6ff;}
.user-avatar-sm{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.user-label{font-size:12px;font-weight:600;line-height:1.2;}
.user-unit{font-size:10px;color:var(--text-muted);}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;border:none;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s;}
.btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(26,58,107,.3);}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--text);}
</style>

<div class="form-card">
    <form method="POST" action="{{ route('disposisi.store') }}">
        @csrf

        <!-- Pilih Surat -->
        <div class="form-group">
            <label>Surat yang Didisposisi <span class="req">*</span></label>
            @if($suratMasuk)
                <input type="hidden" name="surat_masuk_id" value="{{ $suratMasuk->id }}">
                <div class="surat-preview">
                    <strong>{{ $suratMasuk->perihal }}</strong>
                    <span>No: {{ $suratMasuk->nomor_surat }} · Dari: {{ $suratMasuk->pengirim }} · {{ $suratMasuk->tanggal_terima->format('d M Y') }}</span>
                </div>
            @else
                <select name="surat_masuk_id" class="form-control {{ $errors->has('surat_masuk_id')?'err':'' }}"
                    onchange="updateSuratPreview(this)">
                    <option value="">-- Pilih Surat --</option>
                    @foreach($suratMasukList as $sm)
                        <option value="{{ $sm->id }}" {{ old('surat_masuk_id')==$sm->id?'selected':'' }}
                            data-info="{{ $sm->nomor_surat }} · {{ $sm->pengirim }} · {{ $sm->tanggal_terima->format('d M Y') }}">
                            {{ Str::limit($sm->perihal, 60) }}
                        </option>
                    @endforeach
                </select>
                @error('surat_masuk_id')<div class="err-msg">{{ $message }}</div>@enderror
            @endif
        </div>

        <!-- Isi Disposisi -->
        <div class="form-group">
            <label>Isi Disposisi / Perintah <span class="req">*</span></label>
            <textarea name="isi_disposisi" class="form-control {{ $errors->has('isi_disposisi')?'err':'' }}" rows="4"
                placeholder="Tuliskan instruksi atau perintah tindak lanjut secara jelas...">{{ old('isi_disposisi') }}</textarea>
            @error('isi_disposisi')<div class="err-msg">{{ $message }}</div>@enderror
        </div>

        <!-- Penerima (multi-select) -->
        <div class="form-group">
            <label>
                Penerima Disposisi <span class="req">*</span>
                <span style="font-weight:400;color:var(--text-muted);margin-left:8px;" id="selectedCount">(0 dipilih)</span>
            </label>
            @error('penerima_ids')<div class="err-msg" style="margin-bottom:8px;">{{ $message }}</div>@enderror
            <div style="margin-bottom:8px;">
                <input type="text" placeholder="Cari nama..." onkeyup="filterUsers(this.value)"
                    style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
            </div>
            <div class="user-grid" id="userGrid">
                @foreach($userList as $u)
                <label class="user-check {{ in_array($u->id, old('penerima_ids',[])) ? 'selected' : '' }}" id="uc-{{ $u->id }}">
                    <input type="checkbox" name="penerima_ids[]" value="{{ $u->id }}"
                        {{ in_array($u->id, old('penerima_ids',[])) ? 'checked' : '' }}
                        onchange="toggleSelected(this, 'uc-{{ $u->id }}')">
                    <div class="user-avatar-sm">{{ $u->initials }}</div>
                    <div>
                        <div class="user-label">{{ $u->display_name }}</div>
                        <div class="user-unit">{{ $u->unitKerja?->nama ?? $u->roles->first()?->nama_role }}</div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Deadline -->
        <div class="form-group" style="max-width:280px;">
            <label>Deadline (Opsional)</label>
            <input type="date" name="tanggal_deadline" class="form-control"
                value="{{ old('tanggal_deadline') }}" min="{{ date('Y-m-d') }}">
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send-fill"></i> Kirim Disposisi
            </button>
            <a href="{{ route('disposisi.index') }}" class="btn btn-outline">
                <i class="bi bi-arrow-left"></i> Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleSelected(checkbox, id) {
    const el = document.getElementById(id);
    el.classList.toggle('selected', checkbox.checked);
    updateCount();
}
function updateCount() {
    const n = document.querySelectorAll('#userGrid input[type=checkbox]:checked').length;
    document.getElementById('selectedCount').textContent = `(${n} dipilih)`;
}
function filterUsers(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#userGrid .user-check').forEach(el => {
        el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
// Init count
updateCount();
</script>
@endpush
@endsection
