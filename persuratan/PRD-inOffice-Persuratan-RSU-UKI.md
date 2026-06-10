# Product Requirement Document (PRD)
## Aplikasi inOffice Persuratan & Disposisi
### RSU Universitas Kristen Indonesia

---

| **Dokumen** | |
|---|---|
| **Versi** | 1.0 |
| **Tanggal** | 31 Maret 2026 |
| **Penyusun** | PT Integra Teknologi Solusi (SEVIMA Group) |
| **Produk** | inOffice Persuratan & Disposisi (SaaS) |
| **Model Lisensi** | Subscription-based (12 bulan minimum) |
| **Platform** | Web + Mobile (Android & iOS) |

---

## 1. Executive Summary

### 1.1 Ringkasan Produk

inOffice Persuratan & Disposisi adalah aplikasi berbasis web dan mobile untuk mengelola seluruh siklus hidup surat-menyurat dan disposisi secara digital di lingkungan RSU Universitas Kristen Indonesia. Aplikasi ini menggantikan sistem manual berbasis kertas menjadi alur kerja digital yang terintegrasi, real-time, dan terdokumentasi.

### 1.2 Value Proposition

| Pain Point Saat Ini | Solusi inOffice |
|---|---|
| Surat fisik menumpuk, bisa hilang/rusak | Digitalisasi surat (scan & upload) + arsip database |
| Disposisi lambat, perlu kurir fisik | Disposisi real-time via aplikasi |
| Sulit mencari surat lama | Search engine multi-kriteria + arsip sistematis |
| Tidak ada tracking status surat | Tracking progress real-time |
| Penomoran surat manual | Penomoran surat keluar otomatis |
| Monitoring kinerja subjektif | Laporan statistik + penilaian kinerja objektif |
| Kolaborasi draft surat terpisah | Editor kolaboratif mirip MS Word dalam aplikasi |
| Akses terbatas di kantor | Mobile app Android & iOS — anywhere, anytime |

---

## 2. Problem Statement

### 2.1 Kondisi Saat Ini (As-Is)

RSU Universitas Kristen Indonesia menghadapi tantangan dalam tata kelola persuratan:

1. **Inefisiensi operasional** — surat fisik membutuhkan kurir internal untuk distribusi, menambah waktu tempuh dokumen antar unit kerja.
2. **Risiko kehilangan data** — surat fisik rentan hilang, rusak, atau terekspos pihak tidak berwenang.
3. **Sulitnya pelacakan** — tidak ada sistem tracking yang transparan untuk mengetahui status surat apakah sudah dibaca, didisposisi, atau ditindaklanjuti.
4. **Keterbatasan akses** — pengambilan keputusan terhambat karena pimpinan harus hadir fisik untuk membaca dan mendisposisi surat.
5. **Tidak ada audit trail** — histori penanganan surat tidak tercatat sistematis, menyulitkan evaluasi dan audit.
6. **Kolaborasi draft surat tidak efisien** — pembuatan surat keluar masih bolak-balik via email/chat, revisi tidak terpusat.

### 2.2 Target Kondisi (To-Be)

Seluruh proses persuratan dan disposisi berjalan secara digital:

- Surat masuk → diinput operator → tersedia real-time untuk pimpinan
- Pimpinan mendisposisi surat dari mana saja (web/mobile)
- Bawahan menerima, menindaklanjuti, dan melaporkan progres via sistem
- Semua aktivitas tercatat, bisa dimonitor, dan dapat diaudit
- Surat keluar dibuat secara kolaboratif dalam sistem dengan approval berjenjang
- Paperless — minimalisasi penggunaan kertas

---

## 3. Goals & Success Metrics

### 3.1 Product Goals

| Goal | Deskripsi |
|---|---|
| **G1** | Digitalisasi 100% proses surat masuk, surat keluar, dan disposisi |
| **G2** | Mempercepat waktu distribusi surat dari hari → real-time |
| **G3** | Menyediakan sistem tracking dan monitoring yang transparan |
| **G4** | Memungkinkan akses persuratan dari mana saja (web + mobile) |
| **G5** | Mendukung kolaborasi pembuatan surat keluar dalam satu platform |

### 3.2 Key Performance Indicators (KPI)

| KPI | Target | Cara Ukur |
|---|---|---|
| Waktu distribusi surat ke penerima | < 5 menit setelah input | Timestamp input → timestamp terkirim |
| Waktu disposisi pimpinan | < 1 hari kerja | Timestamp surat masuk → timestamp disposisi |
| Rasio surat terdokumentasi digital | 100% | Jumlah surat digital / total surat |
| User adoption rate | ≥ 80% dalam 3 bulan | Active users / total registered users |
| Kecepatan pencarian surat | < 10 detik | Waktu dari search query → hasil muncul |
| Kendala/downtime sistem | ≤ 2 jam/bulan | Monitoring uptime |

---

## 4. User Personas & Stakeholders

| Persona | Deskripsi | Kebutuhan Utama |
|---|---|---|
| **Operator Persuratan** | Staf yang menerima, menginput, dan mengarsipkan surat | Input surat cepat, scan/upload mudah, pencarian arsip |
| **Pimpinan / Direktur** | Atasan yang menerima dan mendisposisi surat | Akses mobile, proses disposisi cepat, monitoring bawahan |
| **Kepala Unit / Manajer** | Penerima disposisi yang menindaklanjuti dan melapor | Notifikasi tugas, penerusan disposisi, laporan progress |
| **Staf Pelaksana** | Eksekutor tindak lanjut surat | Lihat tugas, upload bukti pelaksanaan |
| **Admin IT** | Pengelola sistem dan user management | Manajemen user, role, dan unit kerja |
| **Auditor Internal** | Pihak yang mengaudit tata kelola persuratan | Akses riwayat, laporan statistik, audit trail |

---

## 5. Functional Requirements

### 5.1 Modul Surat Masuk & Surat Keluar

#### 5.1.1 Surat Masuk

| ID | Requirement | Priority | Detail |
|---|---|---|---|
| **FR-SM-01** | Pendataan surat masuk | P0 | Input: nomor surat, tanggal surat, pengirim, perihal, sifat surat, ringkasan isi |
| **FR-SM-02** | Upload digitalisasi surat | P0 | Upload hasil scan (PDF/JPG/PNG) atau file digital |
| **FR-SM-03** | Klasifikasi surat | P1 | Berdasarkan sifat surat: biasa, penting, rahasia, segera |
| **FR-SM-04** | Penanda status surat | P0 | Kode warna: belum dibaca (merah), sudah dibaca (kuning), sudah didisposisi (biru), selesai (hijau) |
| **FR-SM-05** | Tautan surat (linking) | P2 | Menghubungkan surat baru dengan surat sebelumnya yang terkait |
| **FR-SM-06** | Display per page | P2 | Konfigurasi jumlah surat tampil per halaman (10/25/50/100) |
| **FR-SM-07** | Riwayat status surat | P0 | Timeline: diterima → dibaca → didisposisi → diteruskan → dibalas → selesai |

#### 5.1.2 Surat Keluar

| ID | Requirement | Priority | Detail |
|---|---|---|---|
| **FR-SK-01** | Penomoran surat otomatis | P0 | Format nomor sesuai aturan RSU UKI, increment otomatis, tidak bisa duplikat |
| **FR-SK-02** | Surat internal | P1 | Surat keluar yang ditujukan ke unit kerja internal |
| **FR-SK-03** | Klasifikasi surat keluar | P1 | Sama seperti surat masuk |
| **FR-SK-04** | Template surat | P1 | Template siap pakai untuk berbagai jenis surat resmi |

#### 5.1.3 Search & Filter

| ID | Requirement | Priority | Detail |
|---|---|---|---|
| **FR-SF-01** | Full-text search | P0 | Cari berdasarkan: judul, pengirim, penerima, tanggal, perihal, isi disposisi, ringkasan |
| **FR-SF-02** | Advanced filtering | P1 | Filter: rentang tanggal, status, sifat surat, unit kerja |
| **FR-SF-03** | Sorting | P1 | Sortir: tanggal terbaru/terlama, pengirim, status |

#### 5.1.4 Tracking & Notifikasi

| ID | Requirement | Priority | Detail |
|---|---|---|---|
| **FR-TN-01** | Tracking progress surat | P0 | Timeline visual posisi surat saat ini dan history |
| **FR-TN-02** | Notifikasi email | P0 | Email otomatis saat surat masuk, disposisi baru, laporan diterima |
| **FR-TN-03** | Notifikasi in-app | P1 | Alert dalam aplikasi saat ada surat/disposisi baru |
| **FR-TN-04** | Push notification (mobile) | P1 | Push notif ke HP saat ada aktivitas terkait user |

### 5.2 Modul Disposisi

| ID | Requirement | Priority | Detail |
|---|---|---|---|
| **FR-DP-01** | Pembuatan disposisi | P0 | Pimpinan membuat perintah tindak lanjut ke penerima, bisa multi-penerima |
| **FR-DP-02** | Penerusan disposisi (cascade) | P0 | Penerima bisa meneruskan disposisi ke bawahannya (multi-level hierarchy) |
| **FR-DP-03** | Pembatalan disposisi | P1 | Pembuat disposisi bisa membatalkan disposisi yang belum dieksekusi |
| **FR-DP-04** | Laporan pelaksanaan | P0 | Penerima melaporkan hasil tindak lanjut, pemberi bisa menanggapi (approve/reject/comment) |
| **FR-DP-05** | Notifikasi disposisi | P0 | Email + in-app notification saat menerima disposisi baru |
| **FR-DP-06** | Deadline disposisi | P2 | Set tenggat waktu, reminder otomatis menjelang deadline |
| **FR-DP-07** | Search & sort disposisi | P1 | Pencarian dan sortir multi-aspek di modul disposisi |

### 5.3 Modul Draft / Konsep Surat Keluar

| ID | Requirement | Priority | Detail |
|---|---|---|---|
| **FR-DR-01** | Pembuatan draft surat | P0 | Bikin draft surat baru dari template atau blank |
| **FR-DR-02** | Upload DOCX | P0 | Upload file Microsoft Word (.docx) ke dalam aplikasi |
| **FR-DR-03** | Editor WYSIWYG | P0 | Edit dokumen online dengan tampilan dan fitur menyerupai MS Word |
| **FR-DR-04** | Kolaborasi draft | P1 | Beberapa user bisa berkontribusi dalam satu draft (bergantian) |
| **FR-DR-05** | Approval workflow | P0 | Draft → Review → Revisi → Approved → Jadi Surat Keluar Resmi |
| **FR-DR-06** | Version history | P2 | Track perubahan draft, revert ke versi sebelumnya |

#### 5.3.1 Detail Kemampuan Editor DOCX

| ID | Menu | Fitur |
|---|---|---|
| **FR-ED-01** | Home | Font type, font size, bold, italic, underline, warna teks, highlighter, alignment, bullet & numbering |
| **FR-ED-02** | Insert | Page break, tabel (dengan merge/split cell), chart/grafik, shapes, header & footer, gambar dari komputer, penyesuaian tata letak gambar |
| **FR-ED-03** | Layout | Margin, orientasi (portrait/landscape), ukuran kertas (A4/F4/Letter), kolom, section break, penomoran baris |
| **FR-ED-04** | References | Daftar isi otomatis (TOC), footnote, hyperlink, bookmark, caption gambar/tabel, referensi silang, daftar gambar |
| **FR-ED-05** | View | Zoom in/out, tema tampilan (light/dark), navigasi halaman, rulers, show/hide toolbar, status bar |

### 5.4 Modul Manajemen Hak Akses

| ID | Requirement | Priority | Detail |
|---|---|---|---|
| **FR-HA-01** | Manajemen unit kerja | P0 | CRUD unit kerja dalam struktur hierarki, fleksibel tanpa developer |
| **FR-HA-02** | Manajemen user | P0 | Tambah/edit/hapus/nonaktifkan user, password terenkripsi |
| **FR-HA-03** | Role-based access control (RBAC) | P0 | Role: Super Admin, Admin, Pimpinan, Kepala Unit, Operator, Viewer |
| **FR-HA-04** | Hierarki akses data | P0 | Atasan bisa lihat data bawahannya, tidak sebaliknya (top-down) |
| **FR-HA-05** | Multi-role user | P2 | Satu user bisa punya beberapa role sekaligus |
| **FR-HA-06** | Session management | P1 | Auto-logout setelah idle, max session duration |

### 5.5 Modul Laporan & Monitoring

| ID | Requirement | Priority | Detail |
|---|---|---|---|
| **FR-LP-01** | Laporan daftar surat masuk | P0 | Rekap surat masuk dalam rentang waktu, export PDF/Excel |
| **FR-LP-02** | Laporan daftar surat keluar | P0 | Rekap surat keluar, export PDF/Excel |
| **FR-LP-03** | Laporan disposisi masuk & keluar | P1 | Rekap aktivitas disposisi per user/unit |
| **FR-LP-04** | Statistik surat | P1 | Dashboard grafik: volume surat per bulan, per unit, per status |
| **FR-LP-05** | Laporan penilaian kinerja | P0 | Skor kinerja per user: jumlah surat diproses, kecepatan respons, status penyelesaian |

#### 5.5.1 Detail Penilaian Kinerja

| Metrik | Cara Hitung |
|---|---|
| **Volume** | Jumlah surat ditangani per user per periode |
| **Kecepatan** | Rata-rata waktu dari surat diterima → ditindaklanjuti |
| **Ketuntasan** | % surat yang statusnya selesai dari total yang diterima |
| **Kepatuhan Deadline** | % disposisi selesai tepat waktu |
| **Skor Gabungan** | Bobot: Volume (20%) + Kecepatan (30%) + Ketuntasan (30%) + Deadline (20%) |

---

## 6. Non-Functional Requirements

### 6.1 Performa

| ID | Requirement | Target |
|---|---|---|
| **NFR-PF-01** | Waktu muat halaman (page load) | < 3 detik pada koneksi 10 Mbps |
| **NFR-PF-02** | Waktu pencarian surat | < 5 detik untuk database 100.000+ surat |
| **NFR-PF-03** | Concurrent users | Mendukung 50+ user simultan tanpa degradasi |
| **NFR-PF-04** | Upload file | Mendukung lampiran hingga 75 GB per file |

### 6.2 Keamanan

| ID | Requirement | Detail |
|---|---|---|
| **NFR-SC-01** | Autentikasi | Username + password terenkripsi (bcrypt/argon2) |
| **NFR-SC-02** | Proteksi OWASP Top 10 | XSS, SQL Injection, CSRF, broken access control |
| **NFR-SC-03** | Enkripsi data in-transit | HTTPS/TLS 1.3 minimum |
| **NFR-SC-04** | Enkripsi data at-rest | Database encryption untuk data sensitif |
| **NFR-SC-05** | Audit log | Seluruh aktivitas user tercatat dengan timestamp + IP |
| **NFR-SC-06** | Akses hierarkis | User hanya bisa mengakses surat sesuai level kewenangan (top-down) |

### 6.3 Ketersediaan & Keandalan

| ID | Requirement | Target |
|---|---|---|
| **NFR-AV-01** | Uptime | 99.5% (maks downtime 3.6 jam/bulan) |
| **NFR-AV-02** | Backup data | Harian, retensi 30 hari |
| **NFR-AV-03** | Disaster recovery | RPO < 24 jam, RTO < 8 jam |

### 6.4 Usability

| ID | Requirement | Detail |
|---|---|---|
| **NFR-UB-01** | Bahasa | Bahasa Indonesia (seluruh UI dan konten) |
| **NFR-UB-02** | User-friendly | Desain intuitif, tidak perlu pelatihan > 2 jam |
| **NFR-UB-03** | Responsive design | Mendukung desktop (1366px+), tablet, dan mobile browser |

### 6.5 Kompatibilitas

| ID | Requirement | Detail |
|---|---|---|
| **NFR-CP-01** | Browser support | Chrome, Firefox, Edge, Safari — 2 versi terakhir |
| **NFR-CP-02** | Mobile platform | Android 8+, iOS 14+ |
| **NFR-CP-03** | Integrasi | Web Service SOAP untuk integrasi eksternal |

---

## 7. Technical Architecture

### 7.1 Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP (Laravel) |
| **Database** | PostgreSQL |
| **Frontend** | HTML5, CSS3, jQuery, AJAX |
| **Mobile** | Android native / iOS native |
| **Integrasi** | SOAP Web Service |
| **Deployment** | SaaS — dikelola sepenuhnya oleh PT Integra Teknologi Solusi |

### 7.2 Arsitektur Sistem (High-Level)

```
┌──────────────────────────────────────────────────┐
│                    USERS                          │
│  (Desktop Browser / Mobile App Android & iOS)     │
└──────────────┬───────────────────────────────────┘
               │ HTTPS / TLS
┌──────────────▼───────────────────────────────────┐
│              LOAD BALANCER                       │
└──────────────┬───────────────────────────────────┘
               │
┌──────────────▼───────────────────────────────────┐
│           WEB SERVER (PHP/Laravel)               │
│  ┌──────────┬──────────┬──────────┬───────────┐  │
│  │ Surat    │ Disposisi│ Draft    │ Laporan   │  │
│  │ Module   │ Module   │ Module   │ Module    │  │
│  └──────────┴──────────┴──────────┴───────────┘  │
│  ┌──────────────────────────────────────────┐    │
│  │        RBAC & Security Layer             │    │
│  └──────────────────────────────────────────┘    │
└──────────────┬───────────────────────────────────┘
               │
┌──────────────▼───────────────────────────────────┐
│       DATABASE SERVER (PostgreSQL)               │
│  ┌─────────────┬──────────────┬──────────────┐   │
│  │ User & Role │  Surat Data  │ Log & Audit  │   │
│  └─────────────┴──────────────┴──────────────┘   │
└──────────────┬───────────────────────────────────┘
               │
┌──────────────▼───────────────────────────────────┐
│         FILE STORAGE (55 GB allocated)           │
│   (Scan surat, lampiran, draft DOCX, export)     │
└──────────────────────────────────────────────────┘
```

### 7.3 Data Model (Konseptual)

| Entity | Atribut Utama |
|---|---|
| **User** | id, username, password_hash, nama_lengkap, email, unit_kerja_id, role_id[], is_active, last_login |
| **Unit Kerja** | id, nama, parent_id (self-referencing), level, is_active |
| **Role** | id, nama_role, permissions[], description |
| **Surat Masuk** | id, nomor_surat, tanggal_surat, pengirim, perihal, sifat, ringkasan, file_path, status, tautan_surat_id[], unit_kerja_id, created_by |
| **Surat Keluar** | id, nomor_surat_otomatis, tanggal, penerima, perihal, sifat, isi, file_path, status, draft_id, approved_by |
| **Disposisi** | id, surat_id, dari_user_id, ke_user_id[], isi_disposisi, status, tanggal_deadline, parent_disposisi_id |
| **Laporan Disposisi** | id, disposisi_id, dari_user_id, isi_laporan, file_bukti[], status (draft/terkirim), tanggapan |
| **Draft Surat** | id, judul, template_id, file_docx, status (draft/review/revisi/approved), created_by, version |
| **Log Aktivitas** | id, user_id, action, entity_type, entity_id, detail, ip_address, timestamp |

---

## 8. UX / UI Requirements

### 8.1 Prinsip Desain

- **Minimalis & Fungsional** — tidak ada elemen tidak perlu, setiap tombol dan menu punya tujuan jelas
- **Konsisten** — pola navigasi, warna, dan terminologi seragam antar modul
- **Responsif** — optimal di desktop dan nyaman di layar kecil
- **Aksesibilitas** — kontras warna cukup, ukuran font minimum 14px, label jelas

### 8.2 Dashboard Utama (Setelah Login)

| Area | Konten |
|---|---|
| **Header** | Logo RSU UKI + inOffice, nama user, role, foto profil, notifikasi (bell icon), logout |
| **Sidebar** | Menu navigasi: Dashboard, Surat Masuk, Surat Keluar, Disposisi, Draft Surat, Laporan, Admin (jika role admin) |
| **Main Content** | Ringkasan: jumlah surat masuk belum dibaca, disposisi pending, deadline hari ini, statistik mini |
| **Quick Actions** | Tombol cepat: + Surat Masuk Baru, + Disposisi Baru, + Draft Surat |

### 8.3 Tampilan Mobile

- Navigasi bawah (bottom tab): Surat Masuk, Disposisi, Surat Keluar, Laporan, Profil
- Swipe actions: swipe kiri untuk disposisi, swipe kanan untuk arsip
- Offline draft: simpan draft surat saat offline, sync saat online

---

## 9. Workflows

### 9.1 Workflow Surat Masuk → Disposisi → Selesai

```
[Operator]                [Pimpinan]              [Kepala Unit]           [Staf]
    │                          │                       │                    │
    │ 1. Input surat masuk     │                       │                    │
    │    + scan upload         │                       │                    │
    │──────────────────────►   │                       │                    │
    │                          │                       │                    │
    │                    2. Notifikasi surat            │                    │
    │                    baru (email + in-app)          │                    │
    │                          │                       │                    │
    │                    3. Baca surat                  │                    │
    │                    → Status: "Dibaca"             │                    │
    │                          │                       │                    │
    │                    4. Buat disposisi              │                    │
    │                    → Status: "Didisposisi"        │                    │
    │───────────────────────────────────────────────►  │                    │
    │                          │                 5. Notifikasi              │
    │                          │                 disposisi baru             │
    │                          │                       │                    │
    │                          │                 6. Teruskan disposisi       │
    │                          │                 ke staf (opsional)         │
    │                          │                       │──────────────────► │
    │                          │                       │             7. Notifikasi
    │                          │                       │                    │
    │                          │                       │             8. Eksekusi +
    │                          │                       │             laporan progress
    │                          │                       │◄────────────────── │
    │                          │                       │                    │
    │                          │                 9. Review laporan          │
    │                          │                 staf + teruskan ke         │
    │                          │                 pimpinan                   │
    │                          │◄────────────────────── │                    │
    │                          │                       │                    │
    │                    10. Tanggapan/Approve         │                    │
    │                    → Status: "Selesai"           │                    │
```

### 9.2 Workflow Draft Surat Keluar

```
[User]              [Reviewer/Kepala Unit]       [Pimpinan]
  │                        │                         │
  │ 1. Buat draft        │                         │
  │    (blank/template)   │                         │
  │    atau upload DOCX   │                         │
  │─────────────────────► │                         │
  │                        │                         │
  │                  2. Review draft                 │
  │                  → Approve?                      │
  │                        │                         │
  │                   [NO] │                         │
  │◄───────────────────────│                         │
  │ 3. Revisi draft       │                         │
  │─────────────────────► │                         │
  │                        │                         │
  │                  [YES] │                         │
  │                        │────────────────────────►│
  │                        │                   4. Final approval
  │                        │◄────────────────────────│
  │                        │                         │
  │                  5. Generate nomor               │
  │                  surat otomatis                  │
  │                  → Status: "Surat Keluar"        │
```

---

## 10. Deployment & Timeline

### 10.1 Lingkup Pekerjaan

| Tahap | Aktivitas | Minggu 1 | Minggu 2 | Minggu 3 | Minggu 4 |
|---|---|---|---|---|---|
| **1. Analisis** | Analisa dan integrasi aturan persuratan RSU UKI | ████████ | | | |
| **2. Konfigurasi** | Konfigurasi master data & kustomisasi | | ████████ | | |
| **3. Uji Coba** | Uji coba dengan Tim IT RSU UKI, penyesuaian minor | | | ████████ | |
| **4. Go Live** | Sosialisasi pengguna, Training of Trainer (TOT) | | | | ████████ |
| **5. Support** | Masa support berlangganan 12 bulan | | | | ████████ → |

### 10.2 Mode Deploy: SaaS (Software as a Service)

- **Server & Infrastruktur**: Dikelola penuh oleh PT Integra Teknologi Solusi
- **Maintenance**: Update rutin, patch keamanan, perbaikan bug termasuk dalam langganan
- **RSU UKI tidak perlu**: beli server, install software, setup database, atau maintain infrastruktur

---

## 11. Support & SLA

### 11.1 Layanan Customer Service

| Item | Detail |
|---|---|
| **Jam operasional** | Senin–Jumat, 09:00–16:00 WIB |
| **Channel** | Telepon, email, WhatsApp |
| **Respon di luar jam kerja** | Maksimal 1×24 jam |
| **Cakupan** | Bug fixing, tanya jawab teknis, bantuan penggunaan |
| **Tidak termasuk** | Libur nasional dan cuti bersama |

### 11.2 Paket Layanan — Gold (RSU UKI)

| Item | Volume |
|---|---|
| **Durasi langganan** | 12 bulan |
| **Harga paket** | Rp 3.000.000/tahun |
| **Jumlah user** | 50 user manajemen |
| **Aplikasi mobile Android** | Included |
| **Aplikasi mobile iOS** | Included |
| **Editor DOCX mirip MS Word** | Included |
| **Storage** | 55 GB |
| **Maksimal lampiran** | 75 GB per file |
| **Sosialisasi online** | 1 kali oleh PT Integra |
| **Total harga** | Rp 36.000.000 |
| **PPN 11%** | Rp 3.960.000 |
| **Grand total** | **Rp 39.960.000** |

---

## 12. Acceptance Criteria (Go-Live)

| ID | Kriteria | Metode Verifikasi |
|---|---|---|
| **AC-01** | Seluruh modul (Surat Masuk, Surat Keluar, Disposisi, Draft, Laporan) berfungsi sesuai FR | UAT dengan skenario test |
| **AC-02** | 50 user berhasil dibuat dan login | Login test massal |
| **AC-03** | Struktur unit kerja RSU UKI sudah terkonfigurasi | Verifikasi dengan bagan organisasi |
| **AC-04** | Dokumen uji (≥20 surat) berhasil diinput, didisposisi, dan dilaporkan | UAT end-to-end |
| **AC-05** | Editor DOCX dapat upload, edit, simpan, dan export | UAT modul Draft |
| **AC-06** | Laporan statistik menampilkan data akurat | Bandingkan output laporan dengan data manual |
| **AC-07** | Aplikasi mobile berfungsi di Android dan iOS | Install + uji fungsional dasar |
| **AC-08** | TOT selesai dilaksanakan, peserta mampu mengoperasikan sistem | Post-training survey |
| **AC-09** | Tidak ada bug critical/blocker yang tersisa | Bug tracker review |
| **AC-10** | Sistem sudah live dan bisa diakses oleh pengguna | Go-live checklist |

---

## 13. Risks & Mitigations

| Risiko | Dampak | Probabilitas | Mitigasi |
|---|---|---|---|
| **Resistensi user** — pengguna terbiasa manual | Tinggi | Sedang | TOT + sosialisasi intensif, user-friendly UI, dukungan CS responsif |
| **Koneksi internet tidak stabil** | Sedang | Sedang | Aplikasi mobile punya fitur draft offline; RSU pastikan koneksi minimum |
| **Data migration dari sistem lama** | Sedang | Rendah | Tim Integra bantu input/migrasi data awal |
| **Kompleksitas struktur organisasi** | Sedang | Sedang | Fleksibilitas unit kerja di modul Admin, konfigurasi bertahap |
| **Keterlambatan timeline** | Rendah | Rendah | Komunikasi proaktif, timeline realistis dengan buffer |
| **Keamanan data medis/pasien** | Tinggi | Rendah | Enkripsi, RBAC ketat, tidak ada integrasi data pasien (aplikasi ini hanya persuratan administratif) |

---

## 14. Glossary

| Istilah | Definisi |
|---|---|
| **Disposisi** | Perintah/arahan dari atasan kepada bawahan untuk menindaklanjuti suatu surat |
| **Surat Internal** | Surat yang dikirim dari satu unit kerja ke unit kerja lain di dalam RSU UKI |
| **Draft/Konsep** | Rancangan surat keluar yang belum final dan belum mendapat nomor surat resmi |
| **SaaS** | Software as a Service — layanan aplikasi berbasis cloud, user tinggal pakai tanpa perlu install server |
| **TOT** | Training of Trainer — pelatihan untuk pengguna kunci yang akan melatih pengguna lain |
| **RBAC** | Role-Based Access Control — sistem hak akses berdasarkan peran/jabatan user |
| **UAT** | User Acceptance Testing — pengujian akhir oleh pengguna sebelum sistem dinyatakan diterima |

---

## 15. Approval

| Pihak | Nama | Tanda Tangan | Tanggal |
|---|---|---|---|
| **PT Integra Teknologi Solusi** | Nugroho Eko Nolocahyo (Plt. Manager Sales Marketing) | | 31 Maret 2026 |
| **RSU Universitas Kristen Indonesia** | ___________________________ | | ___/___/_____ |

---

**Dokumen ini disusun berdasarkan Proposal Penawaran No. 112/PPS.DMS/ITS/III/2026 oleh PT Integra Teknologi Solusi.**
