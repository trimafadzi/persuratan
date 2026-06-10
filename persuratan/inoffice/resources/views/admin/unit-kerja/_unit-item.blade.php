<div style="border-bottom:1px solid #f1f5f9;padding:10px 16px;padding-left:{{ 16 + $depth * 28 }}px;display:flex;align-items:center;gap:10px;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
    @if($depth > 0)
    <i class="bi bi-arrow-return-right" style="color:var(--text-muted);font-size:12px;flex-shrink:0;"></i>
    @endif
    <div style="width:32px;height:32px;background:{{ ['#eff6ff','#fff7ed','#f0fdf4','#fdf4ff'][$depth] ?? '#f1f5f9' }};border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;color:{{ ['#2557a7','#ea580c','#16a34a','#9333ea'][$depth] ?? '#64748b' }};">
        <i class="bi bi-{{ ['building-fill','diagram-2-fill','buildings-fill','building'][$depth] ?? 'circle-fill' }}"></i>
    </div>
    <div style="flex:1;">
        <div style="font-size:13px;font-weight:700;color:var(--text);">{{ $unit->nama }}</div>
        <div style="font-size:11px;color:var(--text-muted);">Kode: {{ $unit->kode }} · Level {{ $unit->level }} {{ !$unit->is_active ? '· <span style="color:#dc2626;">Nonaktif</span>' : '' }}</div>
    </div>
    <div style="display:flex;gap:4px;">
        <a href="{{ route('admin.unit-kerja.edit', $unit->id) }}" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:#fff;border:1px solid var(--border);border-radius:6px;font-size:12px;color:var(--text);text-decoration:none;font-weight:600;" title="Edit"><i class="bi bi-pencil"></i></a>
        <form method="POST" action="{{ route('admin.unit-kerja.destroy', $unit->id) }}" onsubmit="return confirm('Nonaktifkan unit kerja {{ $unit->nama }}?')" style="display:inline;">
            @csrf @method('DELETE')
            <button style="display:inline-flex;align-items:center;padding:5px 8px;background:#fff0f0;border:1px solid #fca5a5;border-radius:6px;font-size:12px;color:#dc2626;cursor:pointer;" title="Nonaktifkan"><i class="bi bi-slash-circle"></i></button>
        </form>
    </div>
</div>
@if($unit->children && $unit->children->count() > 0)
    @foreach($unit->children as $child)
        @include('admin.unit-kerja._unit-item', ['unit' => $child, 'depth' => $depth + 1])
    @endforeach
@endif
