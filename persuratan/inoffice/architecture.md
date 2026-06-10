# Arsitektur Aplikasi inOffice Persuratan RSU UKI

Dokumen ini menjelaskan struktur arsitektur teknis, desain database, pola desain (design pattern) yang digunakan, serta alur sistem pada aplikasi inOffice Persuratan.

## 1. Stack Teknologi

Aplikasi ini dibangun menggunakan arsitektur monolitik modern berbasis ekosistem PHP dengan spesifikasi berikut:
- **Framework**: Laravel 11 (PHP 8.3+)
- **Database**: PostgreSQL (Production) / SQLite (Development lokal)
- **Frontend**: Blade Templating Engine + Vanilla CSS dengan arsitektur UI berbasis komponen custom.
- **Server**: PHP Development Server (Local) / Nginx/Apache (Production).

## 2. Struktur Direktori Utama

Penyusunan kode mengikuti konvensi MVC (Model-View-Controller) Laravel dengan tambahan *service layer* untuk logika yang kompleks:

```text
inoffice/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/         # Manajemen Master Data (User, Role, UnitKerja)
│   │   │   ├── Auth/          # Autentikasi kustom
│   │   │   └── ...            # SuratMasuk, SuratKeluar, Disposisi, Laporan
│   │   ├── Middleware/        # RBAC (CheckRole) & Audit Logging (ActivityLogger)
│   ├── Models/                # Entitas Eloquent dan definisi relasi
│   ├── Services/              # Business logic (mis. NomorSuratService)
├── database/
│   ├── migrations/            # Skema database terstruktur berdasarkan urutan dependensi
│   ├── seeders/               # Data master awal (Role, UnitKerja, Akun Default)
├── resources/
│   ├── views/
│   │   ├── admin/             # View area admin
│   │   ├── disposisi/         # View modul disposisi
│   │   ├── laporan/           # View dashboard laporan
│   │   ├── layouts/           # Template dasar (app.blade.php) dengan sidebar & topbar
│   │   ├── surat-keluar/      # View modul surat keluar
│   │   └── surat-masuk/       # View modul surat masuk
├── routes/
│   └── web.php                # Definisi routing, middleware grouping
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
