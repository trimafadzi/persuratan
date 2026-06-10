# Arsitektur Aplikasi inOffice Persuratan RSU UKI

Dokumen ini menjelaskan struktur arsitektur teknis, desain database, pola desain (design pattern) yang digunakan, serta alur sistem pada aplikasi inOffice Persuratan.

**Terakhir diupdate**: 10 Juni 2026 — Fase 2 (Push Notification & Real-time) selesai.

## 1. Stack Teknologi

Aplikasi ini dibangun menggunakan arsitektur monolitik modern berbasis ekosistem PHP dengan spesifikasi berikut:
- **Framework**: Laravel 13 (PHP 8.3+)
- **Database**: MySQL (Development) / PostgreSQL (Production)
- **Frontend**: Blade Templating Engine + Vanilla CSS dengan arsitektur UI berbasis komponen custom.
- **Server**: PHP Development Server (Local) / Nginx/Apache (Production).
- **Push Notification**: Firebase Cloud Messaging (FCM) via `kreait/laravel-firebase`
- **Real-time**: Laravel Reverb (WebSocket server)
- **API**: RESTful API v1 dengan Sanctum token authentication
- **PWA**: Service Worker + manifest.json untuk akses mobile browser

## 2. Struktur Direktori Utama

Penyusunan kode mengikuti konvensi MVC (Model-View-Controller) Laravel dengan tambahan *service layer* untuk logika yang kompleks:

```text
inoffice/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # Manajemen Master Data (User, Role, UnitKerja)
│   │   │   ├── Api/V1/             # REST API Controllers (BARU - Fase 1)
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── DashboardApiController.php
│   │   │   │   ├── SuratMasukApiController.php
│   │   │   │   ├── SuratKeluarApiController.php
│   │   │   │   ├── DisposisiApiController.php
│   │   │   │   ├── NotifikasiApiController.php
│   │   │   │   ├── LaporanApiController.php
│   │   │   │   └── UserApiController.php
│   │   │   ├── Auth/               # Autentikasi kustom
│   │   │   └── ...                 # SuratMasuk, SuratKeluar, Disposisi, Laporan
│   │   ├── Middleware/             # RBAC (CheckRole) & Audit Logging (ActivityLogger)
│   │   └── Resources/              # API Response Transformers (BARU - Fase 1)
│   ├── Models/                     # Entitas Eloquent dan definisi relasi
│   ├── Services/                   # Business logic
│   │   ├── NomorSuratService.php   # Penomoran surat otomatis (pessimistic locking)
│   │   └── FcmNotificationService.php  # Push notification via FCM (BARU - Fase 2)
│   └── Events/                     # Broadcasting events (BARU - Fase 2)
│       ├── SuratMasukCreated.php
│       ├── DisposisiBaru.php
│       └── LaporanDiterima.php
├── database/
│   ├── migrations/                 # Skema database terstruktur berdasarkan urutan dependensi
│   └── seeders/                    # Data master awal (Role, UnitKerja, Akun Default)
├── resources/
│   └── views/
│       ├── admin/                  # View area admin
│       ├── disposisi/              # View modul disposisi
│       ├── draft/                  # View modul draft surat
│       ├── laporan/                # View dashboard laporan
│       ├── layouts/                # Template dasar (app.blade.php) dengan sidebar & topbar
│       ├── surat-keluar/           # View modul surat keluar
│       └── surat-masuk/            # View modul surat masuk
├── routes/
│   ├── web.php                     # Definisi routing web (session-based)
│   └── api.php                     # Definisi routing API v1 (token-based) (BARU - Fase 1)
├── config/
│   ├── firebase.php                # Firebase config (BARU - Fase 2)
│   └── sanctum.php                 # Sanctum config (BARU - Fase 1)
├── public/
│   ├── manifest.json               # PWA manifest (BARU - Fase 1)
│   ├── sw.js                       # Service Worker (BARU - Fase 1)
│   └── icons/                      # PWA icons (BARU - Fase 1)
└── storage/
    └── app/
        └── firebase/
            └── service-account.json  # Firebase credentials placeholder (BARU - Fase 2)
```

## 3. Desain Database (Schema)

Sistem database dinormalisasi dengan relasi relasional kuat. Tabel utama meliputi:

1. **Autentikasi & Otorisasi**
   - `users`: Informasi kredensial dan profil staf.
   - `roles` & `role_user`: Implementasi *Role-Based Access Control* (Many-to-Many).
   - `unit_kerja`: Struktur hierarki organisasi menggunakan *self-referencing* (parent-child).

2. **Modul Persuratan**
   - `surat_masuk`: Data arsip surat masuk, status (*lifecycle*), sifat surat, dan file scan.
   - `surat_keluar`: Arsip surat keluar dengan nomor surat yang dibuat otomatis (auto-generated).

3. **Modul Disposisi (Workflows)**
   - `disposisi`: Instruksi atau perintah turunan dari surat masuk. Mengandung relasi *self-referencing* untuk mengakomodasi "Disposisi Berantai" (diteruskan).
   - `disposisi_penerima`: Tabel pivot many-to-many pengguna yang menerima disposisi beserta status *read receipt*.
   - `laporan_disposisi`: Bukti penyelesaian disposisi dari bawahan ke atasan, lengkap dengan lampiran file.

4. **Sistem Penunjang**
   - `notifikasi`: Notifikasi in-app untuk pengguna (surat baru, disposisi, laporan).
   - `log_aktivitas`: Rekam jejak audit (Audit Trail) semua aksi krusial pengguna.
   - `nomor_surat_counter`: Tabel *atomic counter* khusus untuk pembuatan nomor surat otomatis tanpa tabrakan.
   - `users.fcm_token`: Kolom untuk menyimpan Firebase Cloud Messaging device token (multi-device, JSON array).

5. **Sistem Notifikasi & Real-time** (BARU - Fase 2)
   - **FCM Push Notification**: Terintegrasi di 4 titik — disposisi baru, surat masuk baru, laporan disposisi, disposisi diteruskan.
   - **Laravel Reverb**: WebSocket server untuk real-time broadcasting.
   - **Event Classes**: `SuratMasukCreated`, `DisposisiBaru`, `LaporanDiterima` — broadcast ke private channels.

## 4. Pola Desain (Design Patterns) & Praktik Terbaik

### 4.1. Pessimistic Locking untuk Penomoran Surat
Masalah umum dalam web aplikasi adalah *race condition* saat meng-*generate* urutan nomor. Aplikasi ini mengatasi ini menggunakan `DB::transaction` dan metode `lockForUpdate()` pada tabel `nomor_surat_counter`.
*Lokasi: `app/Services/NomorSuratService.php`*

### 4.2. Middleware RBAC (CheckRole)
Otorisasi endpoint diproteksi secara terpusat menggunakan *Middleware* yang mengecek properti slug *role* pada relasi tabel Pivot. Terdapat kondisi pintasan (*bypass*) khusus bagi `superadmin`.
*Lokasi: `app/Http/Middleware/CheckRole.php`*

### 4.3. Audit Trail Pattern
Aktivitas pengguna direkam transparan menggunakan middleware khusus yang mencatat HTTP Method, URL rute, Payload JSON, dan IP address ke dalam tabel *log_aktivitas*.
*Lokasi: `app/Http/Middleware/ActivityLogger.php`*

### 4.4. UI Component Architecture (Vanilla CSS)
Aplikasi tidak bergantung pada CSS framework eksternal besar (Tailwind/Bootstrap). Semua gaya CSS dirancang khusus dengan metodologi *CSS Variables* (`--primary`, `--text`, `--border`) di dalam file master (layout). Ini menghasilkan aplikasi yang memuat gaya *instan*, interaksi sangat cepat (*snappy*), namun memiliki estetika *Enterprise Premium*.

### 4.5. FCM Push Notification Service (BARU - Fase 2)
Notifikasi push dikirim ke device mobile melalui Firebase Cloud Messaging. Service ini menangani:
- **Multi-device support**: Token disimpan sebagai JSON array di `users.fcm_token`
- **Graceful degradation**: Jika FCM gagal, notifikasi in-app tetap berjalan
- **4 titik integrasi**: Disposisi baru, surat masuk baru, laporan disposisi, disposisi diteruskan
*Lokasi: `app/Services/FcmNotificationService.php`*

### 4.6. Real-time Broadcasting (BARU - Fase 2)
Laravel Reverb digunakan sebagai WebSocket server untuk real-time update. Event classes mengimplementasikan `ShouldBroadcast` dan mengirim ke private channels:
- `SuratMasukCreated` → channel `pimpinan` dan `admin`
- `DisposisiBaru` → channel `user.{id}` per penerima
- `LaporanDiterima` → channel `user.{id}` pemberi disposisi
*Lokasi: `app/Events/`*

## 5. Alur Data Utama (Data Flows)

### Alur Surat Masuk & Disposisi
1. **Penerimaan**: Staf Sekretariat menginput metadata `surat_masuk` dan mengunggah dokumen (PDF/JPG). Sistem menyetel status default ke `belum_dibaca`.
2. **Review Direksi**: Direktur membuka surat. Sistem otomatis memperbarui status menjadi `dibaca`.
3. **Pembuatan Disposisi**: Direktur memilih surat, mengisi instruksi, dan mencentang penerima (bisa multi-penerima). Status surat masuk berubah ke `didisposisi`.
4. **Tindak Lanjut**: Bawahan menerima notifikasi. Bawahan melaksanakan tugas dan mengirim `laporan_disposisi`.
5. **Konfirmasi**: Atasan me-review laporan. Jika disetujui, disposisi tersebut ditandai `selesai`.

### Alur Surat Keluar
1. Staf menginput form surat keluar tanpa memasukkan nomor surat.
2. Saat proses simpan, sistem secara sinkronis (*synchronous*) memanggil `NomorSuratService`.
3. Sistem mengunci tabel counter, mengambil angka terakhir untuk bulan tersebut, merakit format penomoran: `SK/RSU-UKI/{KODE-UNIT}/{NO}/{BULAN-ROMAWI}/{TAHUN}`, dan menaikkan (increment) counter.
4. Record `surat_keluar` berhasil dibuat dan disimpan.

## 6. Persyaratan Eksekusi
- Direktori `storage/app/public` harus di-*symlink* ke folder `public/storage` (`php artisan storage:link`) agar file scan yang diunggah dapat diakses.
- Ekstensi PHP yang dibutuhkan: `pdo_pgsql` / `pdo_sqlite`, `fileinfo` (untuk validasi mime-type upload file).

## 7. Arsitektur API & Mobile (BARU - Fase 1 & 2)

### 7.1 API Layer
- **Authentication**: Laravel Sanctum token-based (`auth:sanctum` middleware)
- **Versioning**: Prefix `/api/v1` dengan namespace `App\Http\Controllers\Api\V1`
- **Response Format**: API Resources untuk transformasi data konsisten
- **Rate Limiting**: `throttle:api` middleware
- **CORS**: Konfigurasi untuk mobile app domain

### 7.2 Push Notification (FCM)
- **Package**: `kreait/laravel-firebase ^7.2`
- **Credentials**: `storage/app/firebase/service-account.json` (placeholder)
- **Token Management**: Multi-device via JSON array di `users.fcm_token`
- **Endpoints**:
  - `POST /api/v1/auth/fcm-token` — Register device token
  - `DELETE /api/v1/auth/fcm-token` — Unregister device token

### 7.3 Real-time Broadcasting
- **Server**: Laravel Reverb (`laravel/reverb ^1.10`)
- **Connection**: `BROADCAST_CONNECTION=log` (default), ganti ke `reverb` untuk production
- **Channels**: Private channels (`pimpinan`, `admin`, `user.{id}`)
- **Start Command**: `php artisan reverb:start`

### 7.4 PWA (Progressive Web App)
- **Manifest**: `public/manifest.json` — app name, icons, theme color
- **Service Worker**: `public/sw.js` — cache static assets
- **Icons**: `public/icons/icon-192.png`, `public/icons/icon-512.png`
