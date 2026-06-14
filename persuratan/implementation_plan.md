# Rencana Implementasi — Fase 3.9 & 3.10: Fitur Native Mobile & Skeleton Loading

Implementasi peningkatan kegunaan aplikasi mobile (UX) dengan menambahkan fitur integrasi kamera untuk memotret/men-scan dokumen secara langsung, penyimpanan draf formulir secara offline (Offline Drafts), dan Shimmer Skeleton Loading modern sebagai pengganti indikator loading spinner standar.

## User Review Required

> [!IMPORTANT]
> **Penyimpanan Draf Offline:**
> Kami akan mengimplementasikan penyimpanan draf berbasis `AsyncStorage` untuk tiga formulir utama:
> 1. Registrasi Surat Masuk
> 2. Registrasi Surat Keluar
> 3. Pembuatan Disposisi Baru
> 
> Saat halaman formulir dibuka, aplikasi akan mendeteksi draf yang tersimpan secara lokal dan menampilkan banner konfirmasi di atas form untuk memulihkan (*Restore*) atau menghapus (*Delete*) draf tersebut. Tombol **"Simpan Draf"** juga akan disematkan di barisan tombol aksi. Draf akan otomatis dihapus jika form berhasil terkirim ke server.
>
> **Shimmer Loader Tanpa Library Native Tambahan:**
> Untuk menjaga stabilitas build Android/iOS, kami akan merancang komponen `SkeletonLoader` menggunakan animasi native `Animated` bawaan React Native. Komponen ini memiliki variasi layout untuk List, Dashboard, dan Grafik Statistik.

## Open Questions

> [!NOTE]
> 1. **Dukungan Multi-Attachment Kamera pada Kirim Laporan:**
>    Untuk layar kirim laporan disposisi (`DisposisiLaporanScreen`), foto yang diambil menggunakan Kamera akan di-append ke daftar dokumen terpilih agar pengguna dapat mengunggah beberapa dokumen scan/foto secara bersamaan. Apakah alur multi-attachment kamera ini sudah sesuai? *Rekomendasi:* Ya, ini konsisten dengan fitur Galeri Foto.
> 2. **Auto-save vs Manual Save Draft:**
>    Apakah draf sebaiknya di-autosave setiap kali input berubah, atau cukup manual dengan menekan tombol "Simpan Draf"? *Rekomendasi:* Kombinasi tombol "Simpan Draf" manual adalah opsi paling terkontrol dan mencegah performa storage bottleneck di perangkat berspesifikasi rendah.

## Proposed Changes

Aktivitas pengerjaan difokuskan pada direktori `persuratan/InOfficePersuratan/src`.

---

### [Component 1] Pembuatan Komponen Skeleton Shimmer

#### [NEW] [SkeletonLoader.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/components/SkeletonLoader.tsx)
Membuat komponen visual skeleton shimmer modern:
- Animasi looping opacity halus (0.3 <-> 0.7) menggunakan `Animated.loop`.
- Layout komposit siap pakai:
  - `CardListLoader`: Kerangka list item (untuk Surat Masuk/Keluar, Disposisi, dan Notifikasi).
  - `DashboardLoader`: Kerangka stats grid, quick actions, dan recent items.
  - `ChartLoader`: Kerangka mini stats dan bar chart laporan.

---

### [Component 2] Penerapan Skeleton Shimmer Loader pada List & Stats Screen

#### [MODIFY] [DashboardScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/DashboardScreen.tsx)
- Impor `DashboardLoader` dari `../components/SkeletonLoader`.
- Ganti spinner `<ActivityIndicator size="large" />` dengan `<DashboardLoader />` saat status `loading` bernilai true.

#### [MODIFY] [SuratMasukListScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/SuratMasukListScreen.tsx)
- Impor `CardListLoader`.
- Tampilkan `<CardListLoader />` saat memuat data di awal (`loading && !refreshing`).

#### [MODIFY] [SuratKeluarListScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/SuratKeluarListScreen.tsx)
- Impor `CardListLoader`.
- Tampilkan `<CardListLoader />` saat memuat data di awal.

#### [MODIFY] [DisposisiListScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/DisposisiListScreen.tsx)
- Impor `CardListLoader`.
- Tampilkan `<CardListLoader />` saat memuat data di awal.

#### [MODIFY] [NotifikasiScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/NotifikasiScreen.tsx)
- Impor `CardListLoader`.
- Tampilkan `<CardListLoader />` saat memuat data di awal.

#### [MODIFY] [LaporanScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/LaporanScreen.tsx)
- Impor `ChartLoader`.
- Tampilkan `<ChartLoader />` saat data statistik utama sedang di-fetch.

---

### [Component 3] Integrasi Kamera Native & Penyimpanan Draf Offline

#### [MODIFY] [SuratMasukCreateScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/SuratMasukCreateScreen.tsx)
- Impor `launchCamera` dari `react-native-image-picker`.
- Tambahkan aksi "Kamera" di bagian unggah berkas yang memanggil `launchCamera` untuk memotret surat fisik secara langsung.
- Implementasi sistem draf:
  - Simpan field (nomorSurat, pengirim, perihal, tanggalSurat, tanggalTerima, unitKerja, sifat) ke `@draft_surat_masuk` di `AsyncStorage`.
  - Tampilkan banner konfirmasi pemulihan draf di bagian atas layar jika data terdeteksi.
  - Tambahkan tombol "Simpan Draf" di sebelah tombol simpan utama.
  - Bersihkan storage draf jika penyimpanan data berhasil.

#### [MODIFY] [SuratKeluarCreateScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/SuratKeluarCreateScreen.tsx)
- Impor `launchCamera` dari `react-native-image-picker`.
- Tambahkan tombol aksi "Kamera" untuk lampiran berkas surat keluar.
- Implementasi sistem draf menggunakan key `@draft_surat_keluar` untuk menyimpan `penerima`, `perihal`, `sifat`, dan `isi`.
- Menampilkan banner pemulihan draf dan tombol "Simpan Draf".
- Bersihkan storage draf pasca submit sukses.

#### [MODIFY] [DisposisiCreateScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/DisposisiCreateScreen.tsx)
- Implementasi sistem draf menggunakan key `@draft_disposisi` untuk menyimpan form pembuatan disposisi baru (`selectedSuratId`, `selectedPegawaiIds`, `catatan`, `deadline`, `sifat`).
- Menyediakan banner konfirmasi pemulihan dan tombol "Simpan Draf".
- Bersihkan storage draf pasca submit sukses.

#### [MODIFY] [DisposisiLaporanScreen.tsx](file:///root/persuratan/persuratan/InOfficePersuratan/src/screens/DisposisiLaporanScreen.tsx)
- Impor `launchCamera` dari `react-native-image-picker`.
- Tambahkan tombol aksi "📷 Ambil Foto" di barisan pilihan dokumen bukti pendukung.
- Ketukan pada tombol akan memicu kamera, lalu meng-append berkas gambar yang dihasilkan ke dalam daftar file bukti (`selectedFiles`).

---

## Verification Plan

### Automated Tests
- Type Checking: `npx tsc --noEmit` di folder `InOfficePersuratan`.
- Code Linting: `npm run lint`.

### Manual Verification
1. **Verifikasi Kamera**:
   - Buka form Surat Masuk Baru / Surat Keluar Baru / Kirim Laporan Disposisi.
   - Klik tombol kamera, izinkan akses kamera, lalu ambil foto. Pastikan berkas foto berhasil dimasukkan sebagai lampiran.
2. **Verifikasi Draf Offline**:
   - Buka form Surat Masuk Baru, isi beberapa kolom data, lalu klik tombol **"Simpan Draf"**.
   - Kembali ke Dashboard, lalu masuk kembali ke form Surat Masuk.
   - Verifikasi banner pemulihan tampil di bagian atas. Klik **"Pulihkan"**, pastikan seluruh data terisi kembali sesuai draf.
   - Kirim formulir hingga sukses, masuk kembali ke formulir tersebut dan pastikan draf sudah bersih (banner tidak muncul kembali).
3. **Verifikasi Shimmer Effect**:
   - Lakukan navigasi ke Dashboard, Surat Masuk, Surat Keluar, Disposisi, Notifikasi, dan Laporan.
   - Perhatikan efek transisi loading awal. Pastikan kerangka shimmer skeleton tampil mulus dan rapi sebelum digantikan data riil.
