@extends('layouts.app')
@section('title', 'Draft & Konsep Surat')
@section('page-title', 'Draft & Konsep Surat')
@section('page-subtitle', 'Kolaborasi dan persetujuan konsep surat keluar')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap;">
    <div style="display:flex;gap:4px;background:#e2e8f0;padding:4px;border-radius:8px;">
        <a href="{{ route('draft.index', ['tab' => 'saya']) }}" 
           style="padding:6px 16px;text-decoration:none;font-size:13px;font-weight:600;border-radius:6px;transition:all .2s; {{ $tab === 'saya' ? 'background:#fff;color:var(--primary);box-shadow:var(--shadow-sm);' : 'color:var(--text-muted);' }}">
            Draft Saya
        </a>
        @if(auth()->user()->hasPermission('draft.approve') || auth()->user()->hasPermission('draft.review'))
        <a href="{{ route('draft.index', ['tab' => 'persetujuan']) }}" 
           style="padding:6px 16px;text-decoration:none;font-size:13px;font-weight:600;border-radius:6px;transition:all .2s; {{ $tab === 'persetujuan' ? 'background:#fff;color:var(--primary);box-shadow:var(--shadow-sm);' : 'color:var(--text-muted);' }}">
            Butuh Persetujuan
        </a>
        @endif
    </div>

    <a href="{{ route('draft.create') }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:var(--primary);color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;box-shadow:var(--shadow-sm);transition:all .2s;" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background='var(--primary)'">
        <i class="bi bi-file-earmark-plus"></i> Buat Draft Baru
    </a>
</div>

<div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid var(--border);">
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Judul Draft</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Template</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Versi</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Status</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Pembuat</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Update Terakhir</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($draftList as $draft)
            <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:14px 16px;font-size:13px;font-weight:700;color:var(--primary);">
                    <a href="{{ route('draft.show', $draft->id) }}" style="color:inherit;text-decoration:none;">{{ $draft->judul }}</a>
                </td>
                <td style="padding:14px 16px;font-size:13px;color:var(--text);">
                    {{ $draft->template->nama ?? 'Bebas / Tanpa Template' }}
                </td>
                <td style="padding:14px 16px;font-size:13px;font-weight:600;">
                    <span style="display:inline-block;padding:2px 8px;background:#f1f5f9;color:var(--text);border-radius:100px;font-size:11px;">v{{ $draft->version }}</span>
                </td>
                <td style="padding:14px 16px;font-size:13px;">
                    @if($draft->status === 'draft')
                        <span style="display:inline-block;padding:3px 8px;background:#f1f5f9;color:var(--text-muted);border-radius:100px;font-size:11px;font-weight:600;">Draft</span>
                    @elseif($draft->status === 'review')
                        <span style="display:inline-block;padding:3px 8px;background:#eff6ff;color:#1e40af;border-radius:100px;font-size:11px;font-weight:600;">Review</span>
                    @elseif($draft->status === 'revisi')
                        <span style="display:inline-block;padding:3px 8px;background:#fffbeb;color:#b45309;border-radius:100px;font-size:11px;font-weight:600;">Butuh Revisi</span>
                    @elseif($draft->status === 'approved')
                        <span style="display:inline-block;padding:3px 8px;background:#f0fdf4;color:#15803d;border-radius:100px;font-size:11px;font-weight:600;">Disetujui</span>
                    @endif
                </td>
                <td style="padding:14px 16px;font-size:13px;">
                    {{ $draft->creator->nama_lengkap ?? '-' }}
                </td>
                <td style="padding:14px 16px;font-size:13px;color:var(--text-muted);">
                    {{ $draft->updated_at->diffForHumans() }}
                </td>
                <td style="padding:14px 16px;">
                    <div style="display:flex;gap:6px;align-items:center;">
                        <a href="{{ route('draft.show', $draft->id) }}" style="display:inline-flex;align-items:center;padding:5px 8px;background:#f1f5f9;border:1px solid var(--border);border-radius:6px;font-size:12px;color:var(--text);text-decoration:none;" title="Detail"><i class="bi bi-eye"></i></a>
                        
                        @if($draft->status !== 'approved' && $draft->created_by === auth()->id())
                            <a href="{{ route('draft.edit', $draft->id) }}" style="display:inline-flex;align-items:center;padding:5px 8px;background:#fff;border:1px solid var(--border);border-radius:6px;font-size:12px;color:var(--text);text-decoration:none;" title="Edit"><i class="bi bi-pencil"></i></a>
                            
                            @if($draft->status === 'draft' || $draft->status === 'revisi')
                            <form method="POST" action="{{ route('draft.submit-review', $draft->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" style="display:inline-flex;align-items:center;padding:5px 8px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;font-size:12px;color:#1e40af;cursor:pointer;" title="Kirim Review"><i class="bi bi-send-check"></i> Ajukan</button>
                            </form>
                            @endif
                            
                            <form method="POST" action="{{ route('draft.destroy', $draft->id) }}" onsubmit="return confirm('Hapus draft ini?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" style="display:inline-flex;align-items:center;padding:5px 8px;background:#fff0f0;border:1px solid #fca5a5;border-radius:6px;font-size:12px;color:#dc2626;cursor:pointer;" title="Hapus"><i class="bi bi-trash3"></i></button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding:40px;text-align:center;color:var(--text-muted);font-size:13px;">
                    Data kosong
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $draftList->links() }}
</div>
@endsection
