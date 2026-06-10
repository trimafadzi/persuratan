## Fase 2: Push Notification & Real-time — Implementation Notes

### Files Created
- `app/Services/FcmNotificationService.php` — Service untuk kirim push notification via FCM
- `app/Events/SuratMasukCreated.php` — Event untuk real-time broadcast surat masuk baru
- `app/Events/DisposisiBaru.php` — Event untuk real-time broadcast disposisi baru
- `app/Events/LaporanDiterima.php` — Event untuk real-time broadcast laporan disposisi
- `database/migrations/2026_06_10_133541_add_fcm_token_to_users.php` — Migration kolom fcm_token
- `config/firebase.php` — Firebase config (published from package)
- `storage/app/firebase/service-account.json` — Placeholder untuk Firebase credentials

### Files Modified
- `app/Models/User.php` — Added `fcm_token` to `$fillable`
- `app/Http/Controllers/Api/V1/AuthController.php` — Added `registerFcmToken()` and `unregisterFcmToken()` methods
- `app/Http/Controllers/Api/V1/DisposisiApiController.php` — Integrated FCM notifications
- `app/Http/Controllers/Api/V1/SuratMasukApiController.php` — Integrated FCM notifications
- `app/Http/Controllers/DisposisiController.php` — Integrated FCM notifications (web)
- `app/Http/Controllers/SuratMasukController.php` — Integrated FCM notifications (web)
- `routes/api.php` — Added FCM token endpoints
- `.env` — Added Firebase credentials config
- `.env.example` — Added Firebase credentials config

### Dependencies Installed
- `kreait/laravel-firebase ^7.2` — Firebase Admin SDK for Laravel
- `laravel/reverb ^1.10` — Laravel WebSocket server for real-time broadcasting

### FCM Notification Points
1. **Disposisi Baru** → `notifyDisposisiBaru()` — saat pimpinan membuat disposisi
2. **Disposisi Diteruskan** → `notifyDisposisiDiteruskan()` — saat disposisi di-forward
3. **Laporan Disposisi** → `notifyLaporanDisposisi()` — saat bawahan mengirim laporan
4. **Surat Masuk Baru** → `notifySuratMasukBaru()` — saat operator input surat masuk (notifikasi ke pimpinan)

### FCM Token Management
- Multi-device support: tokens stored as JSON array in `fcm_token` column
- `registerToken()` — adds token if not already present
- `unregisterToken()` — removes token from array
- Graceful degradation: if FCM fails, in-app notification still works

### Real-time Events
- `SuratMasukCreated` → broadcasts to `pimpinan` and `admin` channels
- `DisposisiBaru` → broadcasts to specific user channels (`user.{id}`)
- `LaporanDiterima` → broadcasts to disposisi giver's channel

### Testing
- All 27 existing tests pass
- No new errors from lsp_diagnostics

### Next Steps (for production)
1. Setup Firebase project at console.firebase.google.com
2. Download service account JSON and place in `storage/app/firebase/`
3. Configure `FIREBASE_PROJECT_ID` in `.env`
4. Start Reverb server: `php artisan reverb:start`
5. Configure frontend to listen to WebSocket events
