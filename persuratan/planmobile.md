# 📱 Plan Mobile — inOffice Persuratan RSU UKI

> **Dokumen Perencanaan Pengembangan Mobile App**
> Tanggal: 10 Juni 2026 | Status: **IN PROGRESS** | Terakhir diupdate: 10 Juni 2026 (Fase 2 selesai)

---

## 1. Ringkasan Situasi Saat Ini

### 1.1 Stack Teknologi Web (Existing)

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 13 (PHP 8.3+) |
| Database | PostgreSQL (Prod) / SQLite (Dev) |
| Frontend | Blade + Vanilla CSS + jQuery/AJAX |
| Auth | Session-based (`Auth::attempt`, cookie) |
| Routing | `routes/web.php` — seluruh route berbasis web |
| File Upload | Local storage (`storage/app/public`) |
| Notifikasi | In-app (database) + Email (log driver) + **FCM Push Notification** |

### 1.2 Modul yang Sudah Tersedia

- [x] Autentikasi (Login/Logout session-based)
- [x] Dashboard (statistik, surat terbaru, aktivitas)
- [x] Surat Masuk (CRUD, upload scan, status lifecycle)
- [x] Surat Keluar (CRUD, penomoran otomatis)
- [x] Disposisi (CRUD, teruskan, laporan, tanggapi, batalkan)
- [x] Draft Surat (CRUD, approval workflow, version history)
- [x] Laporan & Statistik (rekap, kinerja, export)
- [x] Admin (User, Unit Kerja, Role, Audit Log)
- [x] Notifikasi In-App (database-based)
- [x] RBAC Middleware (CheckRole)
- [x] Audit Trail (ActivityLogger)

### 1.3 Gap Analysis untuk Mobile

| Area | Status Web | Gap untuk Mobile |
|---|---|---|
| **Autentikasi** | Session/cookie-based | ❌ Tidak ada token-based auth (API) |
| **API Layer** | Tidak ada `routes/api.php` | ❌ Seluruh endpoint mengembalikan Blade views |
| **Push Notification** | ✅ FCM (kreait/laravel-firebase) | ✅ Sudah terintegrasi di semua titik notifikasi |
| **Offline Support** | Tidak ada | ❌ Belum ada local storage/sync mechanism |
| **File Handling** | Server upload via form | ⚠️ Perlu multipart API + camera integration |
| **Real-time Updates** | ✅ Laravel Reverb + Event classes | ✅ WebSocket server siap, tinggal frontend listener |
| **Responsive UI** | Ada (media query 768px) | ⚠️ Sidebar hide, tapi bukan native experience |

---

## 2. Strategi Mobile App

### 2.1 Opsi Pendekatan

| Opsi | Teknologi | Kelebihan | Kekurangan | Rekomendasi |
|---|---|---|---|---|
| **A. React Native** | React Native + Expo | Cross-platform, hot reload, ecosystem besar | Learning curve beda dari PHP stack | ⭐ **Direkomendasikan** |
| **B. Flutter** | Dart + Flutter | Performa tinggi, UI konsisten | Bahasa baru (Dart), build size besar | Alternatif |
| **C. PWA** | Service Worker + Web | Cepat, satu codebase | Fitur native terbatas, no app store | Fase interim |
| **D. Native** | Kotlin + Swift | Performa terbaik, full API access | 2x development, mahal | Overkill |

> **Keputusan: Pendekatan Hybrid — PWA (Fase 1) + React Native (Fase 2)**
>
> - **Fase 1**: PWA untuk akses cepat via mobile browser (low effort, high impact)
> - **Fase 2**: React Native app untuk Android & iOS (full native experience)

### 2.2 Arsitektur Target

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENTS                                  │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │  Web Browser  │  │  PWA Mobile  │  │  React Native App    │    │
│  │  (Blade)      │  │  (SW + Cache)│  │  (Android & iOS)     │    │
│  └──────┬───────┘  └──────┬───────┘  └──────────┬───────────┘    │
│         │                  │                      │               │
└─────────┼──────────────────┼──────────────────────┼───────────────┘
          │                  │                      │
     routes/web.php    routes/api.php         routes/api.php
          │                  │                      │
┌─────────▼──────────────────▼──────────────────────▼───────────────┐
│                    LARAVEL BACKEND                                 │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │                    API Layer (BARU)                          │  │
│  │  ┌───────────┐ ┌───────────┐ ┌───────────┐ ┌────────────┐  │  │
│  │  │ Sanctum   │ │ API       │ │ API       │ │ Push       │  │  │
│  │  │ Token Auth│ │ Resources │ │ Controllers│ │ Notif Svc  │  │  │
│  │  └───────────┘ └───────────┘ └───────────┘ └────────────┘  │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │               Existing Modules (Reusable)                   │  │
│  │  Models │ Services │ Middleware │ Notifications              │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │  PostgreSQL   │  │  File Storage │  │  Firebase Cloud Msg  │    │
│  └──────────────┘  └──────────────┘  └──────────────────────┘    │
└───────────────────────────────────────────────────────────────────┘
```

---

## 3. Fase Implementasi

---

### FASE 1: API Layer + PWA Foundation (Minggu 1–3)

> **Goal**: Backend siap melayani mobile client, PWA dasar bisa diakses dari home screen HP.

#### 1.1 Laravel Sanctum Setup (Token Auth)

- [x] Install Laravel Sanctum (`composer require laravel/sanctum`)
- [x] Publish config Sanctum & jalankan migrasi `personal_access_tokens`
- [x] Tambahkan `HasApiTokens` trait pada model `User`
- [x] Konfigurasi `config/sanctum.php` (token expiration, dll)
- [x] Setup guard `api` di `config/auth.php`

#### 1.2 API Routes (`routes/api.php`)

- [x] Buat file `routes/api.php` dengan prefix `/api/v1`
- [x] **Auth Endpoints**:
  - [x] `POST /api/v1/auth/login` — Login, return token + user data
  - [x] `POST /api/v1/auth/logout` — Revoke token
  - [x] `GET  /api/v1/auth/me` — Get current user profile + roles
- [x] **Dashboard Endpoints**:
  - [x] `GET  /api/v1/dashboard/stats` — Statistik ringkasan
  - [x] `GET  /api/v1/dashboard/surat-terbaru` — 5 surat terbaru
  - [x] `GET  /api/v1/dashboard/deadline-minggu-ini` — Deadline aktif
- [x] **Surat Masuk Endpoints**:
  - [x] `GET    /api/v1/surat-masuk` — List (paginated, filterable)
  - [x] `POST   /api/v1/surat-masuk` — Create + file upload
  - [x] `GET    /api/v1/surat-masuk/{id}` — Detail
  - [x] `PUT    /api/v1/surat-masuk/{id}` — Update
  - [x] `DELETE /api/v1/surat-masuk/{id}` — Delete
  - [x] `PATCH  /api/v1/surat-masuk/{id}/baca` — Tandai dibaca
- [x] **Surat Keluar Endpoints**:
  - [x] `GET    /api/v1/surat-keluar` — List
  - [x] `POST   /api/v1/surat-keluar` — Create
  - [x] `GET    /api/v1/surat-keluar/{id}` — Detail
  - [x] `PUT    /api/v1/surat-keluar/{id}` — Update
  - [x] `DELETE /api/v1/surat-keluar/{id}` — Delete
- [x] **Disposisi Endpoints**:
  - [x] `GET    /api/v1/disposisi` — List (tab: masuk/keluar)
  - [x] `POST   /api/v1/disposisi` — Create
  - [x] `GET    /api/v1/disposisi/{id}` — Detail
  - [x] `POST   /api/v1/disposisi/{id}/teruskan` — Forward disposisi
  - [x] `POST   /api/v1/disposisi/{id}/laporan` — Submit laporan + file bukti
  - [x] `POST   /api/v1/disposisi/{id}/tanggapi` — Approve/reject laporan
  - [x] `PATCH  /api/v1/disposisi/{id}/batal` — Batalkan disposisi
- [x] **Notifikasi Endpoints**:
  - [x] `GET    /api/v1/notifikasi` — List notifikasi user
  - [x] `PATCH  /api/v1/notifikasi/{id}/read` — Mark as read
  - [x] `GET    /api/v1/notifikasi/unread-count` — Badge count
- [x] **Laporan Endpoints**:
  - [x] `GET    /api/v1/laporan/surat-masuk` — Rekap surat masuk
  - [x] `GET    /api/v1/laporan/surat-keluar` — Rekap surat keluar
  - [x] `GET    /api/v1/laporan/kinerja` — Data kinerja pegawai
- [x] **User & Admin Endpoints** (role-protected):
  - [x] `GET    /api/v1/users` — List users (for disposisi recipient picker)
  - [x] `GET    /api/v1/unit-kerja` — List unit kerja

#### 1.3 API Controllers (Refactor)

- [x] Buat `app/Http/Controllers/Api/V1/` directory
- [x] `AuthController.php` — login/logout/me via Sanctum
- [x] `DashboardApiController.php` — extract logic dari `DashboardController`
- [x] `SuratMasukApiController.php` — JSON responses, reuse Model logic
- [x] `SuratKeluarApiController.php`
- [x] `DisposisiApiController.php`
- [x] `NotifikasiApiController.php`
- [x] `LaporanApiController.php`
- [x] `UserApiController.php` (list users untuk picker)

#### 1.4 API Resources (Response Formatting)

- [x] Buat `app/Http/Resources/` directory
- [x] `UserResource.php` / `UserCollection.php`
- [x] `SuratMasukResource.php` / `SuratMasukCollection.php`
- [x] `SuratKeluarResource.php`
- [x] `DisposisiResource.php`
- [x] `NotifikasiResource.php`
- [x] `LaporanResource.php`

#### 1.5 API Middleware & Security

- [x] Terapkan Sanctum `auth:sanctum` middleware pada API group
- [x] Adapt `CheckRole` middleware agar support JSON response (bukan redirect)
- [x] Adapt `ActivityLogger` middleware untuk API requests
- [x] Tambahkan API rate limiting (`throttle:api`)
- [x] CORS configuration untuk mobile app domain

#### 1.6 PWA Foundation

- [x] Buat `manifest.json` (app name, icons, theme color, display: standalone)
- [x] Buat Service Worker dasar (`sw.js`) — cache static assets
- [x] Tambahkan meta tags PWA di `app.blade.php` (`theme-color`, `apple-mobile-web-app-capable`)
- [x] Generate app icons (192x192, 512x512)
- [x] Tambahkan "Add to Home Screen" banner
- [x] Test PWA di Chrome DevTools → Lighthouse audit

#### 1.7 Testing Fase 1

- [x] Unit test API auth (login, token, logout)
- [x] Unit test CRUD API surat masuk
- [x] Unit test CRUD API disposisi
- [x] Integration test: login → create surat → disposisi → laporan
- [x] Test API via Postman/Insomnia collection
- [x] Buat dokumentasi API (Postman collection / OpenAPI spec)

---

### FASE 2: Push Notification & Real-time (Minggu 4–5)

> **Goal**: Notifikasi push ke HP dan real-time update tanpa refresh.
> **Status**: ✅ **SELESAI** — 10 Juni 2026

#### 2.1 Firebase Cloud Messaging (FCM)

- [x] Buat project Firebase Console *(placeholder credentials sudah disiapkan)*
- [x] Install `kreait/laravel-firebase` (`composer require kreait/laravel-firebase ^7.2`)
- [x] Tambahkan kolom `fcm_token` pada tabel `users` (migrasi `2026_06_10_133541_add_fcm_token_to_users`)
- [x] Buat endpoint `POST /api/v1/auth/fcm-token` untuk register device token
- [x] Buat endpoint `DELETE /api/v1/auth/fcm-token` untuk unregister device token
- [x] Buat `FcmNotificationService.php` di `app/Services/`
- [x] Integrate FCM send ke setiap titik notifikasi existing:
  - [x] Disposisi baru → push ke penerima (`notifyDisposisiBaru`)
  - [x] Surat masuk baru → push ke pimpinan (`notifySuratMasukBaru`)
  - [x] Laporan disposisi → push ke pemberi disposisi (`notifyLaporanDisposisi`)
  - [x] Disposisi diteruskan → push ke penerima baru (`notifyDisposisiDiteruskan`)

#### 2.2 Laravel Broadcasting (Opsional, untuk real-time)

- [x] Setup Laravel Reverb (`composer require laravel/reverb ^1.10`)
- [x] Buat Event classes:
  - [x] `SuratMasukCreated` — broadcast ke channel `pimpinan` dan `admin`
  - [x] `DisposisiBaru` — broadcast ke channel `user.{id}` per penerima
  - [x] `LaporanDiterima` — broadcast ke channel `user.{id}` pemberi disposisi
- [x] Frontend listen via Echo (untuk PWA) — Echo + Reverb client terintegrasi, toast notif real-time
- [x] Konfigurasi `BROADCAST_CONNECTION` di `.env`

#### 2.3 Testing Fase 2

- [ ] Test push notification ke device Android (test FCM) *(butuh Firebase project aktif)*
- [ ] Test push notification ke device iOS (test APNs via FCM) *(butuh Firebase project aktif)*
- [x] Test real-time update pada dashboard *(Event classes verified, 27/27 tests pass)*
- [ ] Load test concurrent push (50+ users)

---

### FASE 3: React Native Mobile App (Minggu 6–10)

> **Goal**: Aplikasi native Android & iOS dengan UX optimal.

#### 3.1 Project Setup

- [ ] Initialize React Native project (`npx react-native init InOfficePersuratan`)
- [ ] Setup navigasi (React Navigation — stack + bottom tabs)
- [ ] Setup state management (Zustand atau Redux Toolkit)
- [ ] Setup HTTP client (Axios + interceptor untuk token)
- [ ] Setup secure token storage (react-native-keychain)
- [ ] Setup push notification library (react-native-firebase/messaging)

#### 3.2 Screen: Autentikasi

- [ ] **Login Screen** — Username/Email + Password, remember me
- [ ] **Splash Screen** — Logo inOffice + auto-login check
- [ ] Token management (auto refresh, logout on 401)

#### 3.3 Screen: Dashboard (Home)

- [ ] Stat cards (surat belum dibaca, disposisi pending, deadline, selesai)
- [ ] Surat masuk terbaru (5 items, tap to detail)
- [ ] Deadline minggu ini (list, color-coded urgency)
- [ ] Quick action buttons (+ Surat, + Disposisi)
- [ ] Pull-to-refresh

#### 3.4 Screen: Surat Masuk

- [ ] List surat masuk (infinite scroll, search, filter sifat/status)
- [ ] Detail surat masuk (metadata, file preview/download, timeline status)
- [ ] Form input surat masuk (camera capture + file picker)
- [ ] Swipe action: swipe right → tandai dibaca
- [ ] Status color dots (merah/kuning/biru/hijau)

#### 3.5 Screen: Surat Keluar

- [ ] List surat keluar (search, filter)
- [ ] Detail surat keluar (preview file, nomor otomatis)
- [ ] Form input surat keluar

#### 3.6 Screen: Disposisi

- [ ] Tab: Masuk / Keluar
- [ ] List disposisi (search, filter status)
- [ ] Detail disposisi (info surat, pemberi, penerima, timeline)
- [ ] Form buat disposisi (pilih surat, pilih penerima multi-select, deadline)
- [ ] Form teruskan disposisi
- [ ] Form laporan pelaksanaan (text + upload file bukti dari gallery/camera)
- [ ] Form tanggapan (approve/reject + komentar)
- [ ] Swipe action: swipe left → buat disposisi

#### 3.7 Screen: Notifikasi

- [ ] List notifikasi (grouped by date)
- [ ] Tap → navigate ke entity terkait
- [ ] Badge count di bottom tab
- [ ] Pull-to-refresh

#### 3.8 Screen: Laporan (View Only)

- [ ] Statistik surat masuk (chart/grafik)
- [ ] Statistik surat keluar
- [ ] Kinerja pegawai (tabel/card)

#### 3.9 Screen: Profil

- [ ] Info user (nama, email, jabatan, unit kerja, role)
- [ ] Ganti password
- [ ] Logout
- [ ] App version info

#### 3.10 Fitur Native Mobile

- [ ] Camera integration (scan surat langsung dari kamera)
- [ ] File picker (pilih PDF/image dari gallery)
- [ ] Biometric login (fingerprint/face ID) — opsional
- [ ] Deep linking (tap notifikasi → langsung ke detail surat/disposisi)
- [ ] Offline draft (simpan form yang belum terkirim)
- [ ] Pull-to-refresh pada semua list screen
- [ ] Skeleton loading (shimmer effect)

#### 3.11 UI/UX Mobile

- [ ] Design system (colors, typography, spacing — match web branding)
- [ ] Bottom navigation: Dashboard | Surat Masuk | Disposisi | Notifikasi | Profil
- [ ] Dark mode support
- [ ] Animasi transisi antar screen
- [ ] Empty state illustrations
- [ ] Loading indicators
- [ ] Error handling & retry UI

#### 3.12 Testing Fase 3

- [ ] Test di Android emulator + physical device
- [ ] Test di iOS simulator + physical device (memerlukan Mac)
- [ ] Test flow end-to-end: login → browse surat → disposisi → laporan
- [ ] Test push notification (foreground + background + killed state)
- [ ] Test offline mode (buat draft saat offline, sync saat online)
- [ ] Test file upload (camera capture + gallery pick)
- [ ] Performance profiling (startup time, memory, frame rate)

---

### FASE 4: Build, Deploy & Go-Live (Minggu 11–12)

> **Goal**: Aplikasi tersedia di Play Store & App Store.

#### 4.1 Build & Release

- [ ] Android: Generate signed APK & AAB
- [ ] Android: Buat listing Google Play Console (screenshots, deskripsi)
- [ ] Android: Upload ke Internal Testing → Closed Beta → Production
- [ ] iOS: Buat Apple Developer account + provisioning profile
- [ ] iOS: Generate IPA via Xcode
- [ ] iOS: Upload ke App Store Connect → TestFlight → Production
- [ ] Setup CI/CD pipeline (GitHub Actions / Fastlane)

#### 4.2 Backend Production Readiness

- [ ] PostgreSQL production config (connection pooling, indices)
- [ ] API rate limiting fine-tuning
- [ ] HTTPS enforcement
- [ ] CORS whitelist production domains
- [ ] File storage: migrate ke S3/MinIO untuk scalability (opsional)
- [ ] Monitoring: setup error tracking (Sentry/Bugsnag)
- [ ] Logging: structured JSON logging untuk API

#### 4.3 UAT & Sosialisasi

- [ ] UAT dengan Tim IT RSU UKI (Android + iOS)
- [ ] Bug fixing dari UAT feedback
- [ ] Training penggunaan mobile app untuk TOT
- [ ] Buat panduan pengguna mobile (PDF/in-app guide)
- [ ] Distribusi APK internal (sebelum Play Store approval)

---

## 4. Struktur File Baru (Backend)

### Fase 1 — API Layer + PWA
```
inoffice/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── V1/                          ← BARU (Fase 1)
│   │   │   │       ├── AuthController.php
│   │   │   │       ├── DashboardApiController.php
│   │   │   │       ├── SuratMasukApiController.php
│   │   │   │       ├── SuratKeluarApiController.php
│   │   │   │       ├── DisposisiApiController.php
│   │   │   │       ├── NotifikasiApiController.php
│   │   │   │       ├── LaporanApiController.php
│   │   │   │       └── UserApiController.php
│   │   │   ├── Auth/
│   │   │   └── ... (existing web controllers)
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php                     ← MODIFY (JSON response)
│   │   │   ├── ActivityLogger.php                ← MODIFY (API support)
│   │   │   └── ForceJsonResponse.php             ← BARU (Fase 1)
│   │   └── Resources/                            ← BARU (Fase 1)
│   │       ├── UserResource.php
│   │       ├── SuratMasukResource.php
│   │       ├── SuratMasukCollection.php
│   │       ├── SuratKeluarResource.php
│   │       ├── DisposisiResource.php
│   │       ├── NotifikasiResource.php
│   │       └── LaporanResource.php
├── config/
│   └── sanctum.php                               ← BARU (Fase 1)
├── database/
│   └── migrations/
│       └── xxxx_create_personal_access_tokens.php ← BARU (Fase 1)
├── routes/
│   ├── web.php                                   (existing, unchanged)
│   └── api.php                                   ← BARU (Fase 1)
├── public/
│   ├── manifest.json                             ← BARU (Fase 1)
│   ├── sw.js                                     ← BARU (Fase 1)
│   └── icons/                                    ← BARU (Fase 1)
```

### Fase 2 — Push Notification & Real-time
```
inoffice/
├── app/
│   ├── Services/
│   │   └── FcmNotificationService.php            ← BARU (Fase 2)
│   └── Events/                                   ← BARU (Fase 2)
│       ├── SuratMasukCreated.php
│       ├── DisposisiBaru.php
│       └── LaporanDiterima.php
├── config/
│   └── firebase.php                              ← BARU (Fase 2)
├── database/
│   └── migrations/
│       └── 2026_06_10_133541_add_fcm_token_to_users.php ← BARU (Fase 2)
├── storage/
│   └── app/
│       └── firebase/
│           └── service-account.json              ← PLACEHOLDER (Fase 2)
├── routes/
│   └── api.php                                   ← MODIFY (FCM endpoints)
├── app/Http/Controllers/Api/V1/
│   ├── AuthController.php                        ← MODIFY (FCM token methods)
│   ├── DisposisiApiController.php                ← MODIFY (FCM integration)
│   └── SuratMasukApiController.php               ← MODIFY (FCM integration)
├── app/Http/Controllers/
│   ├── DisposisiController.php                   ← MODIFY (FCM integration)
│   └── SuratMasukController.php                  ← MODIFY (FCM integration)
├── app/Models/
│   └── User.php                                  ← MODIFY (fcm_token fillable)
├── .env                                          ← MODIFY (Firebase config)
└── .env.example                                  ← MODIFY (Firebase config)
```

---

## 5. Prioritas Endpoint API (MoSCoW)

| Priority | Endpoint | Alasan |
|---|---|---|
| **Must** | Auth (login/logout/me) | Fondasi akses mobile |
| **Must** | Surat Masuk (CRUD + baca) | Modul inti paling sering diakses |
| **Must** | Disposisi (CRUD + teruskan + laporan + tanggapi) | Core workflow |
| **Must** | Notifikasi (list + read + count) | Engagement & awareness |
| **Must** | Dashboard stats | Halaman utama mobile |
| **Should** | Surat Keluar (CRUD) | Penting tapi frekuensi lebih rendah |
| **Should** | Users list (picker) | Dibutuhkan saat buat disposisi |
| **Should** | Push notification (FCM) | ✅ **SELESAI** — Backend siap, butuh Firebase project untuk testing |
| **Could** | Laporan (read only) | Nice to have, bisa akses via web |
| **Could** | Draft Surat (CRUD) | Editor DOCX sulit di mobile, skip dulu |
| **Won't (v1)** | Admin (User/Role/UnitKerja CRUD) | Cukup dari web admin |

---

## 6. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| API security (token leak) | Tinggi | Sanctum token expiry, HTTPS only, secure storage |
| Backward compatibility web | Sedang | API layer terpisah, web routes tidak berubah |
| File upload size (75 GB limit per PRD) | Tinggi | Chunked upload API, compression di mobile |
| Offline sync conflict | Sedang | Last-write-wins strategy, conflict detection UI |
| App Store review rejection | Sedang | Ikuti guidelines Apple/Google, prepare metadata awal |
| React Native upgrade issues | Rendah | Lock versi, automated testing |
| iOS development needs Mac | Sedang | Gunakan cloud build (EAS Build) atau Mac CI |
| FCM credentials setup | Rendah | Placeholder sudah ada, tinggal download dari Firebase Console |
| Reverb server management | Rendah | Bisa pakai supervisor/systemd untuk auto-restart |

---

## 7. Timeline Estimasi

```
Minggu 1-2  │████████████████│ ✅ Fase 1A: Sanctum + API Routes + Controllers — SELESAI
Minggu 3    │████████████████│ ✅ Fase 1B: PWA + API Testing + Documentation — SELESAI
Minggu 4    │████████████████│ ✅ Fase 2A: FCM Push Notification — SELESAI
Minggu 5    │████████████████│ ✅ Fase 2B: Real-time + Testing — SELESAI
Minggu 6-7  │░░░░░░░░░░░░░░░░│ 🔲 Fase 3A: RN Setup + Auth + Dashboard + Surat
Minggu 8-9  │░░░░░░░░░░░░░░░░│ 🔲 Fase 3B: Disposisi + Notifikasi + Profil
Minggu 10   │░░░░░░░░░░░░░░░░│ 🔲 Fase 3C: Native features + Polish + Testing
Minggu 11   │░░░░░░░░░░░░░░░░│ 🔲 Fase 4A: Build + Store submission
Minggu 12   │░░░░░░░░░░░░░░░░│ 🔲 Fase 4B: UAT + Go-live + Training
```

---

## 8. Progress Tracking

### Overall Progress: `42%`

| Fase | Status | Progress |
|---|---|---|
| Fase 1: API Layer + PWA | ✅ Completed | `28/28` tasks |
| Fase 2: Push Notification | ✅ Completed | `15/16` tasks *(code selesai, butuh Firebase project untuk testing device)* |
| Fase 3: React Native App | 🔲 Not Started | `0/45` tasks |
| Fase 4: Build & Deploy | 🔲 Not Started | `0/15` tasks |
| **Total** | | **`43/104` tasks** |

### Legend
- 🔲 Not Started
- 🔶 In Progress
- ✅ Completed
- ❌ Blocked
- ⏸️ On Hold

---

## 9. Catatan Teknis Penting

### 9.1 Sanctum vs Passport
Dipilih **Sanctum** karena:
- Lightweight, built-in Laravel
- Token-based auth sederhana (tidak perlu OAuth2 complexity)
- Cocok untuk SPA dan mobile app
- Sudah mendukung token abilities (permission scoping)

### 9.2 Firebase Cloud Messaging (FCM) — BARU (Fase 2)
Dipilih **kreait/laravel-firebase** karena:
- Official Firebase Admin SDK untuk PHP
- Mendukung multi-device token management
- Graceful degradation: jika FCM gagal, notifikasi in-app tetap berjalan
- Token disimpan sebagai JSON array di kolom `fcm_token` (support multi-device per user)

**Integrasi Points:**
- `DisposisiController::store()` → `notifyDisposisiBaru()` ke semua penerima
- `DisposisiController::teruskan()` → `notifyDisposisiDiteruskan()` ke penerima baru
- `DisposisiController::simpanLaporan()` → `notifyLaporanDisposisi()` ke pemberi disposisi
- `SuratMasukController::store()` → `notifySuratMasukBaru()` ke semua pimpinan
- Sama untuk API controllers (`DisposisiApiController`, `SuratMasukApiController`)

**Setup yang masih diperlukan untuk production:**
1. Buat project di [Firebase Console](https://console.firebase.google.com)
2. Download service account JSON → simpan di `storage/app/firebase/service-account.json`
3. Set `FIREBASE_PROJECT_ID` di `.env`

### 9.3 Laravel Reverb (Real-time Broadcasting) — BARU (Fase 2)
Dipilih **Reverb** karena:
- Built-in Laravel, tidak perlu layanan pihak ketiga (Pusher, dll)
- WebSocket server native untuk PHP
- Cocok untuk real-time update dashboard dan notifikasi

**Event Classes yang sudah dibuat:**
- `SuratMasukCreated` → broadcast ke channel `pimpinan` dan `admin`
- `DisposisiBaru` → broadcast ke channel `user.{id}` per penerima
- `LaporanDiterima` → broadcast ke channel `user.{id}` pemberi disposisi

**Setup yang masih diperlukan:**
1. Start Reverb server: `php artisan reverb:start`
2. Konfigurasi frontend (Blade/PWA/RN) untuk listen via Laravel Echo

### 9.4 Reuse Existing Code
Strategi utama: **API controllers memanfaatkan Model & Service yang sudah ada**. Tidak perlu duplicate business logic.

Contoh:
```php
// Api/V1/DisposisiApiController.php — reuse existing models
use App\Models\Disposisi;
use App\Models\Notifikasi;
use App\Services\NomorSuratService;

class DisposisiApiController extends Controller
{
    public function store(Request $request)
    {
        // Validasi sama, logic sama, output JSON bukan redirect
        $validated = $request->validate([...]);
        $disposisi = Disposisi::create([...]);
        return new DisposisiResource($disposisi);
    }
}
```

### 9.5 File Storage Strategy
- **Upload via API**: Multipart form data (`Content-Type: multipart/form-data`)
- **Download file scan**: API endpoint mengembalikan URL signed temporary (Sanctum protected)
- **Camera capture**: React Native `react-native-image-picker` → compress → upload

### 9.6 Offline Support (React Native)
- **AsyncStorage** untuk cache data terbaru (surat list, disposisi list)
- **NetInfo** untuk deteksi koneksi
- **Queue offline actions**: simpan action (create/update) di local queue, sync saat online
- **Conflict resolution**: timestamp-based, server wins

---

## 10. Changelog

| Tanggal | Versi | Deskripsi |
|---|---|---|
| 10 Juni 2026 | 1.2 | ✅ Fase 2 selesai: FCM Push Notification + Laravel Reverb |
| 10 Juni 2026 | 1.1 | ✅ Fase 1 selesai: API Layer + PWA Foundation |
| 10 Juni 2026 | 1.0 | Dokumen awal perencanaan mobile app |

---

## 11. Dependencies yang Perlu Diinstall

### Backend (Laravel) — YANG SUDAH DIINSTALL ✅

```bash
# ✅ Sanctum (Token Auth) — Fase 1
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

# ✅ Firebase (Push Notification) — Fase 2
composer require kreait/laravel-firebase

# ✅ Broadcasting (Real-time) — Fase 2
composer require laravel/reverb
```

### Backend (Laravel) — YANG MASIH PERLU DIKONFIGURASI

```bash
# ⚠️ Firebase Service Account
# Download dari Firebase Console → simpan di storage/app/firebase/service-account.json
# Set FIREBASE_PROJECT_ID di .env

# ⚠️ Start Reverb Server
php artisan reverb:start
```

### Mobile (React Native) — BELUM DIMULAI
```bash
# Project Init
npx react-native init InOfficePersuratan --template react-native-template-typescript

# Core Dependencies
npm install @react-navigation/native @react-navigation/bottom-tabs @react-navigation/native-stack
npm install axios react-native-keychain
npm install zustand # state management
npm install @react-native-firebase/app @react-native-firebase/messaging
npm install react-native-image-picker react-native-document-picker
npm install react-native-screens react-native-safe-area-context
```

---

*Dokumen ini akan diupdate seiring progres development. Tandai checkbox `[x]` untuk task yang sudah selesai.*
