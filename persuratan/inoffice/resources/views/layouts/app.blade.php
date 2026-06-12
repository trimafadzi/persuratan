<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="user-role" content="{{ auth()->user()->roles->first()->slug ?? '' }}">
    <title>@yield('title', 'Dashboard') — inOffice RSU UKI</title>
    <meta name="description" content="Sistem Persuratan & Disposisi Digital RSU Universitas Kristen Indonesia">

    {{-- ── PWA Meta Tags ──────────────────────────────────────────────── --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e3a5f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="inOffice">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary:       #1a3a6b;
            --primary-light: #2557a7;
            --primary-dark:  #0f2347;
            --accent:        #e63946;
            --accent-light:  #ff6b6b;
            --success:       #2d6a4f;
            --success-light: #40916c;
            --warning:       #e9c46a;
            --warning-dark:  #c49a00;
            --info:          #457b9d;
            --danger:        #e63946;
            --bg:            #f0f4f8;
            --bg-dark:       #1a202c;
            --sidebar-bg:    #0f2347;
            --sidebar-width: 260px;
            --card-bg:       #ffffff;
            --text:          #1a202c;
            --text-muted:    #718096;
            --border:        #e2e8f0;
            --shadow-sm:     0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
            --shadow-md:     0 4px 6px rgba(0,0,0,.07), 0 2px 4px rgba(0,0,0,.06);
            --shadow-lg:     0 10px 15px rgba(0,0,0,.1), 0 4px 6px rgba(0,0,0,.05);
            --radius:        12px;
            --radius-sm:     8px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0; top: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform .3s ease;
        }

        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .sidebar-brand .logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            color: #fff;
            flex-shrink: 0;
        }

        .sidebar-brand .logo-text h1 {
            font-size: 16px; font-weight: 700; color: #fff;
            line-height: 1.2;
        }

        .sidebar-brand .logo-text span {
            font-size: 11px; color: rgba(255,255,255,.5);
            font-weight: 400;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 4px;
        }

        .nav-label {
            font-size: 10px;
            font-weight: 600;
            color: rgba(255,255,255,.3);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px 8px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: rgba(255,255,255,.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all .2s ease;
            margin-bottom: 2px;
            position: relative;
        }

        .nav-item:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }

        .nav-item.active {
            background: rgba(37,87,167,.5);
            color: #fff;
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 20px;
            background: var(--accent-light);
            border-radius: 0 3px 3px 0;
        }

        .nav-item .badge {
            margin-left: auto;
            background: var(--accent);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 100px;
        }

        .nav-item i { width: 20px; font-size: 16px; text-align: center; }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .2s;
        }

        .user-info:hover { background: rgba(255,255,255,.06); }

        .avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .user-info .user-details h4 {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .user-info .user-details span {
            font-size: 11px;
            color: rgba(255,255,255,.5);
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ========== TOPBAR ========== */
        .topbar {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0;
            z-index: 50;
            box-shadow: var(--shadow-sm);
        }

        .topbar-left h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
        }

        .topbar-left p {
            font-size: 12px;
            color: var(--text-muted);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notif-btn {
            position: relative;
            width: 40px; height: 40px;
            border-radius: 50%;
            border: 1px solid var(--border);
            background: transparent;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted);
            font-size: 18px;
            transition: all .2s;
        }

        .notif-btn:hover { background: var(--bg); color: var(--primary); }

        .notif-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            background: var(--accent);
            border-radius: 50%;
            border: 2px solid #fff;
        }

        /* ========== PAGE CONTENT ========== */
        .page-content {
            flex: 1;
            padding: 24px;
        }

        /* ========== STAT CARDS ========== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: all .25s ease;
            cursor: default;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-card .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .stat-card .stat-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }

        .stat-card .stat-icon.red    { background: #fff0f0; color: var(--accent); }
        .stat-card .stat-icon.yellow { background: #fffbeb; color: var(--warning-dark); }
        .stat-card .stat-icon.blue   { background: #eff6ff; color: var(--primary-light); }
        .stat-card .stat-icon.green  { background: #f0fdf4; color: var(--success-light); }

        .stat-card .stat-change {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 100px;
            font-weight: 600;
        }

        .stat-card .stat-change.up   { background: #f0fdf4; color: var(--success-light); }
        .stat-card .stat-change.down { background: #fff0f0; color: var(--accent); }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ========== CONTENT GRID ========== */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 16px;
        }

        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
        }

        /* ========== CARDS ========== */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .card-header h3 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
        }

        .card-header .view-all {
            font-size: 12px;
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 600;
        }

        .card-body { padding: 0; }

        /* ========== SURAT LIST ========== */
        .surat-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            transition: background .15s;
            cursor: pointer;
        }

        .surat-item:last-child { border-bottom: none; }
        .surat-item:hover { background: var(--bg); }

        .surat-status-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-red    { background: var(--danger); }
        .dot-yellow { background: var(--warning-dark); }
        .dot-blue   { background: var(--primary-light); }
        .dot-green  { background: var(--success-light); }

        .surat-info { flex: 1; min-width: 0; }

        .surat-info .surat-perihal {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .surat-info .surat-meta {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .surat-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 100px;
            flex-shrink: 0;
        }

        .badge-segera   { background: #fff0f0; color: var(--danger); }
        .badge-penting  { background: #fffbeb; color: var(--warning-dark); }
        .badge-rahasia  { background: #f5f3ff; color: #7c3aed; }
        .badge-biasa    { background: var(--bg); color: var(--text-muted); }

        /* ========== DISPOSISI ACTIVITY ========== */
        .activity-item {
            display: flex;
            gap: 12px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
        }

        .activity-item:last-child { border-bottom: none; }

        .activity-icon {
            width: 34px; height: 34px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .activity-icon.primary  { background: #eff6ff; color: var(--primary-light); }
        .activity-icon.success  { background: #f0fdf4; color: var(--success-light); }
        .activity-icon.warning  { background: #fffbeb; color: var(--warning-dark); }
        .activity-icon.danger   { background: #fff0f0; color: var(--danger); }

        .activity-info h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 2px;
        }

        .activity-info p {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* ========== QUICK ACTIONS ========== */
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .quick-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
        }

        .quick-btn.primary {
            background: var(--primary);
            color: #fff;
        }

        .quick-btn.primary:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26,58,107,.3);
        }

        .quick-btn.outline {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--text);
        }

        .quick-btn.outline:hover {
            background: var(--bg);
            border-color: var(--primary-light);
            color: var(--primary);
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- ========== SIDEBAR ========== -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-wrap">
            <div class="logo-icon"><i class="bi bi-envelope-paper-fill"></i></div>
            <div class="logo-text">
                <h1>inOffice</h1>
                <span>RSU Universitas Kristen Indonesia</span>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-label">Menu Utama</div>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="{{ route('surat-masuk.index') }}" class="nav-item {{ request()->routeIs('surat-masuk.*') ? 'active' : '' }}">
                <i class="bi bi-inbox-fill"></i> Surat Masuk
                @if(($jumlahBelumDibaca ?? 0) > 0)
                    <span class="badge">{{ $jumlahBelumDibaca }}</span>
                @endif
            </a>
            <a href="{{ route('surat-keluar.index') }}" class="nav-item {{ request()->routeIs('surat-keluar.*') ? 'active' : '' }}">
                <i class="bi bi-send-fill"></i> Surat Keluar
            </a>
            <a href="{{ route('disposisi.index') }}" class="nav-item {{ request()->routeIs('disposisi.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3-fill"></i> Disposisi
                @if(($jumlahDisposisiPending ?? 0) > 0)
                    <span class="badge">{{ $jumlahDisposisiPending }}</span>
                @endif
            </a>
            <a href="{{ route('draft.index') }}" class="nav-item {{ request()->routeIs('draft.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text-fill"></i> Draft Surat
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Laporan</div>
            <a href="{{ route('laporan.index') }}" class="nav-item {{ request()->routeIs('laporan.index') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> Dashboard Laporan
            </a>
            <a href="{{ route('laporan.surat-masuk') }}" class="nav-item {{ request()->routeIs('laporan.surat-masuk') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-arrow-down"></i> Lap. Surat Masuk
            </a>
            <a href="{{ route('laporan.surat-keluar') }}" class="nav-item {{ request()->routeIs('laporan.surat-keluar') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-arrow-up"></i> Lap. Surat Keluar
            </a>
            <a href="{{ route('laporan.kinerja') }}" class="nav-item {{ request()->routeIs('laporan.kinerja') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i> Kinerja Pegawai
            </a>
        </div>

        @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('admin-it'))
        <div class="nav-section">
            <div class="nav-label">Administrasi</div>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Manajemen User
            </a>
            <a href="{{ route('admin.unit-kerja.index') }}" class="nav-item {{ request()->routeIs('admin.unit-kerja.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Unit Kerja
            </a>
            <a href="{{ route('admin.roles.index') }}" class="nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <i class="bi bi-shield-fill"></i> Role & Akses
            </a>
            <a href="{{ route('admin.log.index') }}" class="nav-item {{ request()->routeIs('admin.log.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Audit Log
            </a>
        </div>
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="user-info" onclick="document.getElementById('userMenu').click()">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nama_lengkap ?? auth()->user()->name, 0, 2)) }}</div>
            <div class="user-details">
                <h4>{{ auth()->user()->nama_lengkap ?? auth()->user()->name }}</h4>
                <span>{{ auth()->user()->roles->first()?->nama_role ?? 'User' }}</span>
            </div>
        </div>
        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
            @csrf
        </form>
        <a href="#"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="nav-item" style="color: rgba(255,100,100,.8); margin-top: 8px;">
            <i class="bi bi-box-arrow-right"></i> Keluar
        </a>
    </div>
</aside>

<!-- ========== MAIN ========== -->
<div class="main-content">
    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <h2>@yield('page-title', 'Dashboard')</h2>
            <p>@yield('page-subtitle', 'Selamat datang, ' . (auth()->user()->nama_lengkap ?? auth()->user()->name))</p>
        </div>
        <div class="topbar-right">
            <div style="position: relative;" id="notifContainer">
                <button class="notif-btn" title="Notifikasi" onclick="toggleNotifDropdown(event)" style="border: 1px solid var(--border); background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; color: var(--text-muted); font-size: 18px; transition: all .2s; position: relative;">
                    <i class="bi bi-bell"></i>
                    @if(($unreadNotificationsCount ?? 0) > 0)
                        <span id="notifBadge" style="position: absolute; top: -4px; right: -4px; min-width: 18px; height: 18px; background: var(--accent); color: #fff; border-radius: 999px; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; padding: 0 5px; border: 2px solid #fff;">{{ $unreadNotificationsCount }}</span>
                    @else
                        <span id="notifBadge" style="display: none;"></span>
                    @endif
                </button>
                
                <!-- Dropdown Menu -->
                <div id="notifDropdown" style="display: none; position: absolute; right: 0; top: 48px; width: 320px; background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-lg); z-index: 1000; overflow: hidden;">
                    <div style="padding: 12px 16px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                        <span style="font-size: 13px; font-weight: 700; color: var(--text);">Notifikasi</span>
                        @if(($unreadNotificationsCount ?? 0) > 0)
                            <span style="font-size: 11px; font-weight: 600; padding: 2px 8px; background: var(--accent); color: #fff; border-radius: 100px;">
                                {{ $unreadNotificationsCount }} baru
                            </span>
                        @endif
                    </div>
                    <div class="notif-list" style="max-height: 280px; overflow-y: auto;">
                        @forelse($latestNotifications ?? [] as $notif)
                            <a href="{{ route('notifikasi.read', $notif->id) }}" style="display: flex; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #f1f5f9; text-decoration: none; color: inherit; transition: background .15s; {{ !$notif->is_read ? 'background:#eff6ff;' : '' }}" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='{{ !$notif->is_read ? '#eff6ff' : '#fff' }}'">
                                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0;
                                    @if($notif->tipe === 'disposisi') background:#eff6ff; color:#1e40af;
                                    @elseif($notif->tipe === 'laporan') background:#f0fdf4; color:#166534;
                                    @else background:#fffbeb; color:#854d0e; @endif">
                                    <i class="@if($notif->tipe === 'disposisi') bi bi-diagram-3-fill @elseif($notif->tipe === 'laporan') bi bi-file-earmark-check-fill @else bi bi-bell-fill @endif"></i>
                                </div>
                                <div style="flex: 1; min-width: 0; text-align: left;">
                                    <h5 style="font-size: 12px; font-weight: 700; margin: 0 0 2px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; color: var(--text);">{{ $notif->judul }}</h5>
                                    <p style="font-size: 11px; color: var(--text-muted); margin: 0 0 4px; line-height: 1.3; white-space: normal;">{{ $notif->pesan }}</p>
                                    <span style="font-size: 9px; color: var(--text-muted);">{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @empty
                            <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 12px;">
                                <i class="bi bi-bell-slash" style="font-size: 20px; display: block; margin-bottom: 6px;"></i>
                                Tidak ada notifikasi baru
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">
        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background:#fff0f0;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
                <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
// Mobile sidebar toggle
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

// Notification Dropdown toggle
function toggleNotifDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('notifDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', function(event) {
    const container = document.getElementById('notifContainer');
    const dropdown = document.getElementById('notifDropdown');
    if (container && dropdown && !container.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});
</script>

@vite(['resources/js/app.js', 'resources/js/echo.js'])

@stack('scripts')

{{-- ── PWA Service Worker Registration ───────────────────────────────── --}}
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker
            .register('/sw.js')
            .then(function (registration) {
                console.log('[inOffice SW] Registered, scope:', registration.scope);
            })
            .catch(function (err) {
                console.warn('[inOffice SW] Registration failed:', err);
            });
    });
}
</script>
</body>
</html>
