<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — inOffice RSU UKI</title>
    <meta name="description" content="Login ke Sistem Persuratan & Disposisi Digital RSU UKI">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary:       #1a3a6b;
            --primary-light: #2557a7;
            --accent:        #e63946;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f0f4f8;
        }

        /* Left panel */
        .login-visual {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-dark, #0f2347) 0%, var(--primary) 50%, var(--primary-light) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        .login-visual::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 400px; height: 400px;
            background: rgba(255,255,255,.04);
            border-radius: 50%;
        }

        .login-visual::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 300px; height: 300px;
            background: rgba(255,255,255,.04);
            border-radius: 50%;
        }

        .login-visual .visual-content {
            position: relative; z-index: 1;
            text-align: center;
            color: #fff;
        }

        .login-visual .app-icon {
            width: 80px; height: 80px;
            background: rgba(255,255,255,.15);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px;
            margin: 0 auto 24px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,.2);
        }

        .login-visual h1 {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .login-visual h2 {
            font-size: 16px;
            font-weight: 400;
            opacity: .7;
            margin-bottom: 40px;
        }

        .features-list {
            list-style: none;
            text-align: left;
            width: 100%;
            max-width: 320px;
        }

        .features-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            font-size: 14px;
            opacity: .85;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .features-list li:last-child { border: none; }

        .features-list li i {
            width: 32px; height: 32px;
            background: rgba(255,255,255,.12);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        /* Right panel — form */
        .login-form-wrap {
            width: 480px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 48px;
            background: #fff;
        }

        .login-form-wrap .form-header {
            margin-bottom: 36px;
        }

        .login-form-wrap .form-header h3 {
            font-size: 26px;
            font-weight: 800;
            color: #1a202c;
            margin-bottom: 6px;
        }

        .login-form-wrap .form-header p {
            font-size: 14px;
            color: #718096;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px;
        }

        .form-group .input-wrap {
            position: relative;
        }

        .form-group .input-wrap i {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 16px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #1a202c;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(37,87,167,.1);
        }

        .form-group input.error {
            border-color: var(--accent);
        }

        .form-group .error-msg {
            font-size: 12px;
            color: var(--accent);
            margin-top: 4px;
        }

        .toggle-pass {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
            font-size: 16px;
            background: none;
            border: none;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .remember-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #4a5568;
            cursor: pointer;
        }

        .remember-wrap input[type="checkbox"] {
            accent-color: var(--primary-light);
            width: 16px; height: 16px;
        }

        .forgot-link {
            font-size: 13px;
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 600;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all .2s;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(26,58,107,.35);
        }

        .btn-login:active { transform: translateY(0); }

        .btn-login:disabled {
            opacity: .7;
            cursor: not-allowed;
            transform: none;
        }

        .alert-error {
            background: #fff0f0;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #a0aec0;
        }

        @media (max-width: 768px) {
            .login-visual { display: none; }
            .login-form-wrap { width: 100%; padding: 40px 24px; }
        }
    </style>
</head>
<body>

<!-- Visual Panel -->
<div class="login-visual">
    <div class="visual-content">
        <div class="app-icon"><i class="bi bi-envelope-paper-fill"></i></div>
        <h1>inOffice</h1>
        <h2>Persuratan & Disposisi Digital</h2>

        <ul class="features-list">
            <li><i class="bi bi-inbox-fill"></i> Digitalisasi surat masuk & keluar</li>
            <li><i class="bi bi-diagram-3-fill"></i> Disposisi real-time multi-level</li>
            <li><i class="bi bi-search"></i> Pencarian arsip instan</li>
            <li><i class="bi bi-bar-chart-fill"></i> Laporan & monitoring kinerja</li>
            <li><i class="bi bi-shield-fill-check"></i> Aman dengan enkripsi & audit log</li>
        </ul>
    </div>
</div>

<!-- Form Panel -->
<div class="login-form-wrap">
    <div class="form-header">
        <h3>Selamat Datang 👋</h3>
        <p>Masuk ke sistem persuratan RSU Universitas Kristen Indonesia</p>
    </div>

    @if($errors->any() || session('error'))
    <div class="alert-error">
        <i class="bi bi-exclamation-circle-fill"></i>
        {{ $errors->first() ?? session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <div class="form-group">
            <label for="username">Username / Email</label>
            <div class="input-wrap">
                <i class="bi bi-person"></i>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="Masukkan username atau email"
                    required
                    autocomplete="username"
                    class="{{ $errors->has('username') ? 'error' : '' }}"
                >
            </div>
            @error('username')
                <div class="error-msg">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
                <i class="bi bi-lock"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                    autocomplete="current-password"
                    class="{{ $errors->has('password') ? 'error' : '' }}"
                >
                <button type="button" class="toggle-pass" onclick="togglePassword()" title="Tampilkan password">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
            @error('password')
                <div class="error-msg">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-options">
            <label class="remember-wrap">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Ingat saya
            </label>
        </div>

        <button type="submit" class="btn-login" id="loginBtn">
            <i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem
        </button>
    </form>

    <div class="form-footer">
        &copy; {{ date('Y') }} RSU Universitas Kristen Indonesia &mdash; Powered by inOffice
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Memproses...';
});
</script>
</body>
</html>
