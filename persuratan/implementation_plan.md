# Rencana Implementasi — Fase 3.6: Modul Disposisi (Mobile)

Implementasi lengkap modul Disposisi pada aplikasi mobile, termasuk navigasi stack khusus, daftar disposisi masuk & keluar dengan pencarian/filter, rincian disposisi dengan histori laporan, pembuatan disposisi baru (untuk surat masuk), penerusan disposisi (forward), pengiriman laporan pelaksanaan (dengan unggah berkas bukti), tanggapan atas laporan (approval/rejection), dan pembatalan disposisi.

## User Review Required

> [!IMPORTANT]
> **Integrasi Alur Disposisi:**
> 1. Tab navigasi **Disposisi** akan memuat `DisposisiNavigator` yang mengarahkan ke daftar disposisi masuk/keluar pegawai yang sedang aktif.
> 2. Tombol **Buat Disposisi** akan ditambahkan pada halaman [SuratMasukDetailScreen](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/SuratMasukDetailScreen.tsx) agar pimpinan dapat dengan mudah mendelegasikan surat masuk langsung setelah membacanya.
> 3. Alur status disposisi (`pending` -> `selesai` / `diteruskan` / `dibatalkan`) disinkronkan secara real-time melalui API backend.

## Open Questions

> [!NOTE]
> 1. **Daftar Surat pada Pembuatan Disposisi:** Ketika membuat disposisi langsung dari modul Disposisi (bukan dari detail surat), apakah kita perlu menyediakan picker surat masuk? *Rekomendasi:* Ya, kami menyediakan modal picker berisi daftar surat masuk aktif (belum selesai) yang diambil dari server.
> 2. **Unggah Bukti Laporan:** Laporan pelaksanaan disposisi mendukung lampiran bukti (PDF, Word, JPEG, PNG). Kita akan menggunakan `DocumentPicker` dan `ImagePicker` (galeri gambar) yang sudah terintegrasi di project. Apakah perlu mendukung jepretan kamera langsung? *Rekomendasi:* Menggunakan picker galeri gambar & dokumen sudah cukup memenuhi kebutuhan 75MB limit file upload.

## Proposed Changes

Aktivitas pengerjaan difokuskan pada direktori `persuratan/InOfficePersuratan/src`.

---

### [Component 1] Navigasi & Integrasi Disposisi

#### [NEW] [DisposisiNavigator.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/navigation/DisposisiNavigator.tsx)
Stack navigator khusus untuk alur Disposisi:
- `DisposisiList` (Daftar disposisi masuk & keluar)
- `DisposisiDetail` (Informasi rinci disposisi & tindakan terkait)
- `DisposisiCreate` (Formulir pembuatan disposisi baru)
- `DisposisiForward` (Formulir meneruskan disposisi)
- `DisposisiLaporan` (Formulir pelaporan hasil pelaksanaan)
- `DisposisiTanggapan` (Formulir persetujuan/penolakan laporan)

#### [MODIFY] [MainTabNavigator.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/navigation/MainTabNavigator.tsx)
Mengubah rujukan komponen `DisposisiTab` dari `DisposisiScreen` (placeholder) menjadi `DisposisiNavigator`.

#### [DELETE] [DisposisiScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/DisposisiScreen.tsx)
Menghapus file screen placeholder lama karena tugasnya digantikan oleh `DisposisiListScreen.tsx`.

---

### [Component 2] Tampilan Layar Utama Disposisi

#### [NEW] [DisposisiListScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/DisposisiListScreen.tsx)
Menampilkan daftar disposisi dengan tabs:
- **Disposisi Masuk**: Disposisi yang didelegasikan oleh orang lain kepada user yang login.
- **Disposisi Keluar**: Disposisi yang didelegasikan oleh user yang login kepada bawahan/orang lain.
Fitur:
- Kolom pencarian isi instruksi disposisi.
- Filter horizontal cepat berdasarkan status (`pending`, `diteruskan`, `selesai`, `dibatalkan`).
- Menandai item belum dibaca (unread indicator) untuk disposisi masuk.
- Pagination (*infinite scroll*).

#### [NEW] [DisposisiDetailScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/DisposisiDetailScreen.tsx)
Menampilkan rincian disposisi:
- Metadata surat masuk terkait (nomor, pengirim, perihal).
- Nama pemberi disposisi & daftar penerima (beserta status is_read & tanggal dibaca).
- Instruksi & tenggat waktu (deadline).
- Riwayat laporan pelaksanaan (isi laporan, status tanggapan, file bukti).
- Tombol aksi kontekstual:
  - Penerima: **Teruskan** & **Kirim Laporan**.
  - Pemberi: **Batalkan** (menghapus disposisi) & **Tanggapi Laporan** (jika ada laporan terkirim yang belum disetujui).

---

### [Component 3] Tampilan Formulir Aksi Disposisi

#### [NEW] [DisposisiCreateScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/DisposisiCreateScreen.tsx)
Formulir untuk membuat disposisi baru:
- Surat Masuk picker (jika tidak dilewatkan dari halaman detail surat).
- Penerima picker (mengambil data dari `/api/v1/users` dengan multi-select & pencarian nama).
- Input isi instruksi disposisi.
- Input tanggal tenggat waktu (deadline).

#### [NEW] [DisposisiForwardScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/DisposisiForwardScreen.tsx)
Formulir untuk meneruskan disposisi yang diterima ke bawahan/pegawai lain. Memiliki input instruksi, multi-select penerima, dan deadline.

#### [NEW] [DisposisiLaporanScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/DisposisiLaporanScreen.tsx)
Formulir pelaporan hasil pelaksanaan disposisi:
- Input isi laporan pelaksanaan.
- Unggah file bukti lampiran scan/foto (menggunakan `DocumentPicker` atau `launchImageLibrary`).
- Mengirim request `multipart/form-data` ke `POST /api/v1/disposisi/{id}/laporan`.

#### [NEW] [DisposisiTanggapanScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/DisposisiTanggapanScreen.tsx)
Formulir pimpinan untuk memberikan tanggapan atas laporan bawahan:
- Pilihan status tanggapan: **Setuju (Approved)** atau **Tolak (Rejected)**.
- Catatan tanggapan.

#### [MODIFY] [SuratMasukDetailScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/SuratMasukDetailScreen.tsx)
Menambahkan tombol **Buat Disposisi** di baris aksi bawah (atau di bawah metadata surat) yang mengarahkan user ke screen `DisposisiCreate` dengan parameter `surat_masuk_id`.

---

## Verification Plan

### Automated Tests
- Type checking: `npx tsc --noEmit` di folder `InOfficePersuratan`.
- Code linting: `npm run lint`.

### Manual Verification
- **Akses & Navigasi**: Memastikan menu Tab Disposisi memuat daftar dengan lancar.
- **Delegasi Surat (Pimpinan)**:
  1. Masuk ke Surat Masuk Detail, klik **Buat Disposisi**.
  2. Pilih penerima (bisa multi-select), isi instruksi, dan tetapkan tanggal deadline.
  3. Kirim dan pastikan dialihkan ke daftar disposisi keluar.
- **Penerimaan & Laporan (Bawahan)**:
  1. Login sebagai penerima disposisi, pastikan disposisi baru memiliki indikator belum dibaca.
  2. Klik item untuk membuka detail (pastikan status dibaca terupdate di server).
  3. Klik **Kirim Laporan**, isi teks laporan, unggah file foto bukti, lalu klik kirim.
- **Persetujuan (Pimpinan)**:
  1. Pimpinan menerima notifikasi/melihat laporan di detail disposisi keluar.
  2. Klik **Tanggapi Laporan**, pilih **Setuju**, masukkan catatan.
  3. Pastikan status disposisi berubah menjadi **Selesai**.
