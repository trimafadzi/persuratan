@extends('layouts.app')
@section('title', 'Buat Draft Baru')
@section('page-title', 'Buat Draft Baru')
@section('page-subtitle', 'Buat konsep surat keluar baru dari template atau kosong')

@section('content')
<div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:24px;box-shadow:var(--shadow-sm);max-width:900px;margin:0 auto;">
    <form method="POST" action="{{ route('draft.store') }}">
        @csrf
        
        <div style="margin-bottom:16px;">
            <label for="judul" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--text);">Judul / Perihal Draft *</label>
            <input type="text" name="judul" id="judul" required placeholder="Contoh: Undangan Rapat Koordinasi Bulanan" 
                   style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;outline:none;">
            @error('judul')
                <span style="color:var(--danger);font-size:12px;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:16px;">
            <label for="template_id" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--text);">Pilih Template Dokumen</label>
            <select name="template_id" id="template_id" onchange="loadTemplate()" 
                    style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;outline:none;background:#fff;">
                <option value="">-- Tanpa Template (Mulai dari Kosong) --</option>
                @foreach($templates as $template)
                    <option value="{{ $template->id }}" data-content="{{ json_encode($template->konten_html) }}">{{ $template->nama }} ({{ $template->jenis }})</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:24px;">
            <label for="konten_html" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--text);">Konten / Isi Surat *</label>
            <textarea name="konten_html" id="konten_html" required style="width:100%;height:350px;padding:12px;border:1px solid var(--border);border-radius:8px;font-family:sans-serif;font-size:14px;line-height:1.6;outline:none;resize:vertical;"></textarea>
            <p style="font-size:11px;color:var(--text-muted);margin-top:4px;">Anda dapat menggunakan tag HTML dasar untuk memformat dokumen (misalnya: &lt;h3&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;table&gt;, &lt;tr&gt;, &lt;td&gt;).</p>
            @error('konten_html')
                <span style="color:var(--danger);font-size:12px;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;border-top:1px solid var(--border);padding-top:16px;">
            <a href="{{ route('draft.index') }}" style="padding:10px 20px;background:#f1f5f9;color:var(--text);border:1px solid var(--border);border-radius:8px;font-size:13px;text-decoration:none;font-weight:600;">Kembali</a>
            <button type="submit" style="padding:10px 20px;background:var(--primary);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Simpan Draft</button>
        </div>
    </form>
</div>

<script>
function loadTemplate() {
    const select = document.getElementById('template_id');
    const selectedOption = select.options[select.selectedIndex];
    const textarea = document.getElementById('konten_html');
    
    if (selectedOption.value) {
        try {
            const rawContent = JSON.parse(selectedOption.getAttribute('data-content'));
            textarea.value = rawContent;
        } catch (e) {
            console.error('Failed to parse template content', e);
        }
    } else {
        textarea.value = '';
    }
}
</script>
@endsection
