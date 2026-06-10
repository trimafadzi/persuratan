@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan aktivitas persuratan hari ini')

@section('content')

<!-- Quick Actions -->
<div class="quick-actions">
    <a href="{{ route('surat-masuk.create') }}" class="quick-btn primary">
        <i class="bi bi-plus-circle-fill"></i> Surat Masuk Baru
    </a>
    <a href="{{ route('disposisi.create') }}" class="quick-btn outline">
        <i class="bi bi-diagram-3"></i> Buat Disposisi
    </a>
    <a href="{{ route('draft.create') }}" class="quick-btn outline">
        <i class="bi bi-file-earmark-plus"></i> Draft Surat
    </a>
    <a href="{{ route('laporan.index') }}" class="quick-btn outline">
        <i class="bi bi-download"></i> Export Laporan
    </a>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red"><i class="bi bi-envelope-fill"></i></div>
            <span class="stat-change down">Belum Dibaca</span>
        </div>
        <div class="stat-value">{{ $stats['surat_belum_dibaca'] ?? 0 }}</div>
        <div class="stat-label">Surat Masuk Baru</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow"><i class="bi bi-diagram-3-fill"></i></div>
            <span class="stat-change down">Pending</span>
        </div>
        <div class="stat-value">{{ $stats['disposisi_pending'] ?? 0 }}</div>
        <div class="stat-label">Disposisi Menunggu</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue"><i class="bi bi-calendar-event"></i></div>
            <span class="stat-change {{ ($stats['deadline_hari_ini'] ?? 0) > 0 ? 'down' : 'up' }}">
                Hari Ini
            </span>
        </div>
        <div class="stat-value">{{ $stats['deadline_hari_ini'] ?? 0 }}</div>
        <div class="stat-label">Deadline Hari Ini</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
            <span class="stat-change up">Bulan Ini</span>
        </div>
        <div class="stat-value">{{ $stats['surat_selesai_bulan'] ?? 0 }}</div>
        <div class="stat-label">Surat Selesai</div>
    </div>
</div>

<!-- Content Grid -->
<div class="content-grid">

    <!-- Surat Masuk Terbaru -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-inbox" style="color:var(--primary-light);margin-right:6px;"></i> Surat Masuk Terbaru</h3>
                <a href="{{ route('surat-masuk.index') }}" class="view-all">Lihat Semua →</a>
            </div>
            <div class="card-body">
                @forelse($suratMasukTerbaru ?? [] as $surat)
                <a href="{{ route('surat-masuk.show', $surat->id) }}" class="surat-item" style="text-decoration:none;">
                    <div class="surat-status-dot dot-{{ $surat->status === 'belum_dibaca' ? 'red' : ($surat->status === 'dibaca' ? 'yellow' : ($surat->status === 'didisposisi' ? 'blue' : 'green')) }}"></div>
                    <div class="surat-info">
                        <div class="surat-perihal">{{ $surat->perihal }}</div>
                        <div class="surat-meta">
                            Dari: {{ $surat->pengirim }} &nbsp;·&nbsp;
                            {{ $surat->tanggal_terima->format('d M Y') }}
                        </div>
                    </div>
                    <span class="surat-badge badge-{{ $surat->sifat }}">{{ ucfirst($surat->sifat) }}</span>
                </a>
                @empty
                <div style="padding:32px;text-align:center;color:var(--text-muted);font-size:13px;">
                    <i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;opacity:.3;"></i>
                    Belum ada surat masuk
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Aktivitas Disposisi -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-activity" style="color:var(--accent);margin-right:6px;"></i> Aktivitas Terkini</h3>
                <a href="{{ route('disposisi.index') }}" class="view-all">Lihat Semua →</a>
            </div>
            <div class="card-body">
                @forelse($aktivitasTerkini ?? [] as $aktivitas)
                <div class="activity-item">
                    <div class="activity-icon {{ $aktivitas['warna'] }}">
                        <i class="{{ $aktivitas['icon'] }}"></i>
                    </div>
                    <div class="activity-info">
                        <h4>{{ $aktivitas['judul'] }}</h4>
                        <p>{{ $aktivitas['waktu'] }}</p>
                    </div>
                </div>
                @empty
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">
                    <i class="bi bi-clock-history" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>
                    Belum ada aktivitas
                </div>
                @endforelse
            </div>
        </div>

        <!-- Deadline Minggu Ini -->
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-alarm-fill" style="color:var(--warning-dark);margin-right:6px;"></i> Deadline Minggu Ini</h3>
            </div>
            <div class="card-body">
                @forelse($deadlineMingguIni ?? [] as $d)
                <div class="activity-item">
                    <div class="activity-icon {{ $d->isOverdue() ? 'danger' : 'warning' }}">
                        <i class="bi bi-clock{{ $d->isOverdue() ? '-fill' : '' }}"></i>
                    </div>
                    <div class="activity-info">
                        <h4>{{ Str::limit($d->isi_disposisi, 40) }}</h4>
                        <p>Deadline: {{ $d->tanggal_deadline->format('d M Y') }}
                            {{ $d->isOverdue() ? '⚠️ Terlambat!' : '' }}
                        </p>
                    </div>
                </div>
                @empty
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">
                    <i class="bi bi-check2-all" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>
                    Tidak ada deadline minggu ini
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endsection
