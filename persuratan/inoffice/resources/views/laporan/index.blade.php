@extends('layouts.app')
@section('title','Laporan & Statistik')
@section('page-title','Laporan & Statistik')
@section('page-subtitle','Ringkasan kinerja persuratan RSU UKI')

@section('content')
<style>
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:20px;}
.stat-box{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;box-shadow:var(--shadow-sm);transition:all .25s;}
.stat-box:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);}
.stat-box .icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:10px;}
.stat-box .value{font-size:30px;font-weight:800;line-height:1;}
.stat-box .label{font-size:12px;color:var(--text-muted);margin-top:4px;font-weight:500;}
.chart-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow-sm);margin-bottom:16px;}
.chart-card h3{font-size:14px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.bar-row{display:flex;align-items:center;gap:10px;margin-bottom:8px;}
.bar-label{width:70px;font-size:11px;color:var(--text-muted);text-align:right;flex-shrink:0;}
.bar-track{flex:1;height:20px;background:#f1f5f9;border-radius:100px;overflow:hidden;position:relative;}
.bar-fill{height:100%;border-radius:100px;transition:width .6s ease;display:flex;align-items:center;justify-content:flex-end;padding-right:6px;}
.bar-fill span{font-size:10px;font-weight:700;color:#fff;}
.bar-fill.masuk{background:linear-gradient(90deg,var(--primary),var(--primary-light));}
.bar-fill.keluar{background:linear-gradient(90deg,var(--success),var(--success-light));}
.export-grid{display:flex;gap:10px;flex-wrap:wrap;}
.export-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-weight:600;color:var(--text);text-decoration:none;background:#fff;transition:all .2s;}
.export-btn:hover{border-color:var(--primary-light);color:var(--primary);background:#eff6ff;}
.status-donut{display:flex;flex-wrap:wrap;gap:8px;}
.status-item{display:flex;align-items:center;gap:6px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-weight:600;}
.dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
</style>

<!-- Stat Cards -->
<div class="stat-grid">
    <div class="stat-box">
        <div class="icon" style="background:#fff0f0;color:#dc2626;"><i class="bi bi-inbox-fill"></i></div>
        <div class="value" style="color:#1a202c;">{{ number_format($stats['total_masuk']) }}</div>
        <div class="label">Total Surat Masuk</div>
    </div>
    <div class="stat-box">
        <div class="icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-send-fill"></i></div>
        <div class="value" style="color:#1a202c;">{{ number_format($stats['total_keluar']) }}</div>
        <div class="label">Total Surat Keluar</div>
    </div>
    <div class="stat-box">
        <div class="icon" style="background:#fffbeb;color:#d97706;"><i class="bi bi-diagram-3-fill"></i></div>
        <div class="value" style="color:#1a202c;">{{ number_format($stats['total_disposisi']) }}</div>
        <div class="label">Total Disposisi</div>
    </div>
    <div class="stat-box">
        <div class="icon" style="background:#eff6ff;color:#2557a7;"><i class="bi bi-check-circle-fill"></i></div>
        <div class="value" style="color:#1a202c;">{{ number_format($stats['selesai_bulan']) }}</div>
        <div class="label">Selesai Bulan Ini</div>
    </div>
</div>

<!-- Chart + Status Grid -->
<div style="display:grid;grid-template-columns:1fr 280px;gap:16px;">
    <!-- Volume per bulan -->
    <div class="chart-card">
        <h3><i class="bi bi-bar-chart-fill" style="color:var(--primary-light);"></i> Volume Surat 12 Bulan Terakhir</h3>
        @php $maxVal = collect($volumePerBulan)->max(fn($v) => max($v['masuk'],$v['keluar'])) ?: 1; @endphp
        @foreach($volumePerBulan as $v)
        <div class="bar-row">
            <div class="bar-label">{{ $v['bulan'] }}</div>
            <div style="flex:1;display:flex;flex-direction:column;gap:3px;">
                <div class="bar-track">
                    <div class="bar-fill masuk" style="width:{{ max(4,$v['masuk']/$maxVal*100) }}%">
                        @if($v['masuk'] > 0)<span>{{ $v['masuk'] }}</span>@endif
                    </div>
                </div>
                <div class="bar-track">
                    <div class="bar-fill keluar" style="width:{{ max(2,$v['keluar']/$maxVal*100) }}%">
                        @if($v['keluar'] > 0)<span>{{ $v['keluar'] }}</span>@endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        <div style="display:flex;gap:16px;margin-top:12px;font-size:11px;">
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:8px;background:var(--primary);border-radius:3px;display:inline-block;"></span> Masuk</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:8px;background:var(--success);border-radius:3px;display:inline-block;"></span> Keluar</span>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="chart-card">
        <h3><i class="bi bi-pie-chart-fill" style="color:var(--accent);"></i> Status Surat Masuk</h3>
        <div class="status-donut" style="flex-direction:column;">
            @php
                $statusConfig = ['belum_dibaca'=>['Belum Dibaca','#dc2626'],'dibaca'=>['Dibaca','#d97706'],'didisposisi'=>['Didisposisi','#2557a7'],'selesai'=>['Selesai','#16a34a']];
                $totalSurat = $statusBreakdown->sum() ?: 1;
            @endphp
            @foreach($statusConfig as $key => [$label, $color])
            @php $count = $statusBreakdown->get($key, 0); $pct = round($count/$totalSurat*100); @endphp
            <div class="status-item">
                <span class="dot" style="background:{{ $color }};"></span>
                <span style="flex:1;">{{ $label }}</span>
                <span style="font-size:16px;font-weight:800;color:{{ $color }};">{{ $count }}</span>
                <span style="font-size:10px;color:var(--text-muted);">{{ $pct }}%</span>
            </div>
            @endforeach
        </div>

        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
            <div style="font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:8px;">QUICK EXPORT</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <a href="{{ route('laporan.export', ['type'=>'surat-masuk', 'format'=>'csv']) }}" class="export-btn">
                    <i class="bi bi-download"></i> Surat Masuk (CSV)
                </a>
                <a href="{{ route('laporan.export', ['type'=>'surat-keluar', 'format'=>'csv']) }}" class="export-btn">
                    <i class="bi bi-download"></i> Surat Keluar (CSV)
                </a>
                <a href="{{ route('laporan.kinerja') }}" class="export-btn" style="background:#eff6ff;border-color:var(--primary-light);color:var(--primary);">
                    <i class="bi bi-person-fill-up"></i> Kinerja Pegawai
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
