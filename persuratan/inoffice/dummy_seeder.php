<?php
// Dummy data seeder — run once
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$userId = 1; // superadmin
$now = Carbon::now();

// ====== 10 SURAT MASUK ======
$suratMasuk = [
    ['nomor_surat' => 'B-1234/KS.02/VI/2026', 'tanggal_surat' => '2026-06-01', 'tanggal_terima' => '2026-06-02', 'pengirim' => 'Kementerian Kesehatan RI', 'perihal' => 'Undangan Rapat Koordinasi Nasional Pelayanan Kesehatan', 'sifat' => 'penting', 'ringkasan' => 'Mengundang Direktur RSU UKI untuk menghadiri Rakornas Pelayanan Kesehatan yang akan diselenggarakan pada 15-17 Juni 2026 di Jakarta.', 'status' => 'belum_dibaca', 'unit_kerja_id' => 1],
    ['nomor_surat' => 'HK.02.03/D/4567/2026', 'tanggal_surat' => '2026-06-03', 'tanggal_terima' => '2026-06-04', 'pengirim' => 'BPJS Kesehatan', 'perihal' => 'Pemberitahuan Hasil Akreditasi FKTP RSU UKI', 'sifat' => 'penting', 'ringkasan' => 'Menyampaikan bahwa RSU UKI telah lulus akreditasi FKTP dengan predikat PARIPURNA.', 'status' => 'dibaca', 'unit_kerja_id' => 1],
    ['nomor_surat' => 'SR-001/BPOM/VI/2026', 'tanggal_surat' => '2026-05-28', 'tanggal_terima' => '2026-06-01', 'pengirim' => 'BPOM RI', 'perihal' => 'Inspeksi Mendadak Sarana Produksi Alat Kesehatan', 'sifat' => 'rahasia', 'ringkasan' => 'Pemberitahuan akan dilaksanakannya inspeksi sidak terhadap alat kesehatan yang digunakan di RSU UKI pada 10 Juni 2026.', 'status' => 'didisposisi', 'unit_kerja_id' => 2],
    ['nomor_surat' => 'TU.01.02/890/V/2026', 'tanggal_surat' => '2026-05-25', 'tanggal_terima' => '2026-05-27', 'pengirim' => 'Dinas Kesehatan DKI Jakarta', 'perihal' => 'Permintaan Data Tenaga Medis dan Non-Medis', 'sifat' => 'segera', 'ringkasan' => 'Meminta data update tenaga medis dan non-medis RSU UKI per Mei 2026 untuk keperluan database Dinas Kesehatan.', 'status' => 'selesai', 'unit_kerja_id' => 6],
    ['nomor_surat' => 'KW.01/3456/VI/2026', 'tanggal_surat' => '2026-06-05', 'tanggal_terima' => '2026-06-06', 'pengirim' => 'Pengadilan Negeri Jakarta Timur', 'perihal' => 'Permintaan Visum et Repertum', 'sifat' => 'rahasia', 'ringkasan' => 'Permintaan salinan visum et repertum pasien an. Sdr. Andi Pratama yang dirawat pada 12 April 2026 untuk keperluan persidangan.', 'status' => 'dibaca', 'unit_kerja_id' => 4],
    ['nomor_surat' => 'B-5678/P2P/VI/2026', 'tanggal_surat' => '2026-06-02', 'tanggal_terima' => '2026-06-03', 'pengirim' => 'Dirjen P2P Kemenkes', 'perihal' => 'Pelaporan Kasus DBD dan ISPA', 'sifat' => 'segera', 'ringkasan' => 'Meminta laporan bulanan kasus DBD dan ISPA periode Januari-Mei 2026 untuk evaluasi program pengendalian penyakit.', 'status' => 'belum_dibaca', 'unit_kerja_id' => 4],
    ['nomor_surat' => 'SK-890/BPJS-K/V/2026', 'tanggal_surat' => '2026-05-30', 'tanggal_terima' => '2026-06-01', 'pengirim' => 'BPJS Ketenagakerjaan', 'perihal' => 'Sosialisasi Program JKK dan JKM', 'sifat' => 'biasa', 'ringkasan' => 'Penawaran kerjasama sosialisasi program Jaminan Kecelakaan Kerja dan Jaminan Kematian kepada seluruh pegawai RSU UKI.', 'status' => 'belum_dibaca', 'unit_kerja_id' => 6],
    ['nomor_surat' => 'UND-45/IDI/JT/VI/2026', 'tanggal_surat' => '2026-06-04', 'tanggal_terima' => '2026-06-05', 'pengirim' => 'IDI Cabang Jakarta Timur', 'perihal' => 'Undangan Seminar Etika Kedokteran', 'sifat' => 'biasa', 'ringkasan' => 'Mengundang tenaga medis RSU UKI untuk mengikuti seminar "Etika Kedokteran di Era Digital" pada 25 Juni 2026.', 'status' => 'dibaca', 'unit_kerja_id' => 5],
    ['nomor_surat' => 'S-123/BKN/VI/2026', 'tanggal_surat' => '2026-06-01', 'tanggal_terima' => '2026-06-03', 'pengirim' => 'Badan Kepegawaian Negara', 'perihal' => 'Verifikasi Data PNS di Lingkungan RSU UKI', 'sifat' => 'penting', 'ringkasan' => 'Permintaan verifikasi dan validasi data PNS di lingkungan RSU UKI untuk sinkronisasi database BKN.', 'status' => 'didisposisi', 'unit_kerja_id' => 6],
    ['nomor_surat' => 'PR.01/456/LKPP/VI/2026', 'tanggal_surat' => '2026-06-06', 'tanggal_terima' => '2026-06-07', 'pengirim' => 'LKPP RI', 'perihal' => 'Audit Pengadaan Barang dan Jasa Tahun 2026', 'sifat' => 'penting', 'ringkasan' => 'Pemberitahuan akan dilaksanakannya audit pengadaan barang dan jasa di RSU UKI untuk tahun anggaran 2026.', 'status' => 'belum_dibaca', 'unit_kerja_id' => 7],
];

$suratIds = [];
foreach ($suratMasuk as $i => $s) {
    $id = DB::table('surat_masuk')->insertGetId(array_merge($s, [
        'created_by' => $userId,
        'created_at' => $now->copy()->subDays(10 - $i),
        'updated_at' => $now,
    ]));
    $suratIds[] = $id;
}
echo "✅ 10 Surat Masuk inserted\n";

// ====== 10 SURAT KELUAR ======
$suratKeluar = [
    ['nomor_surat_otomatis' => 'SK/RSU-UKI/DIR/001/VI/2026', 'tanggal' => '2026-06-01', 'penerima' => 'Kementerian Kesehatan RI', 'perihal' => 'Konfirmasi Kehadiran Rakornas Pelayanan Kesehatan', 'sifat' => 'penting', 'status' => 'terkirim'],
    ['nomor_surat_otomatis' => 'SK/RSU-UKI/DIR/002/VI/2026', 'tanggal' => '2026-06-01', 'penerima' => 'Dinas Kesehatan DKI Jakarta', 'perihal' => 'Laporan Bulanan Pelayanan Rawat Jalan dan Rawat Inap', 'sifat' => 'biasa', 'status' => 'terkirim'],
    ['nomor_surat_otomatis' => 'SK/RSU-UKI/DIR/003/VI/2026', 'tanggal' => '2026-06-02', 'penerima' => 'BPJS Kesehatan', 'perihal' => 'Klaim Pembayaran Kapitasi Periode Mei 2026', 'sifat' => 'penting', 'status' => 'terkirim'],
    ['nomor_surat_otomatis' => 'SK/RSU-UKI/WADIR-MED/001/VI/2026', 'tanggal' => '2026-06-02', 'penerima' => 'IDI Cabang Jakarta Timur', 'perihal' => 'Delegasi Peserta Seminar Etika Kedokteran', 'sifat' => 'biasa', 'status' => 'terkirim'],
    ['nomor_surat_otomatis' => 'SK/RSU-UKI/WADIR-UK/001/VI/2026', 'tanggal' => '2026-06-03', 'penerima' => 'BPJS Ketenagakerjaan', 'perihal' => 'Persetujuan Sosialisasi Program JKK dan JKM', 'sifat' => 'biasa', 'status' => 'approved'],
    ['nomor_surat_otomatis' => 'SK/RSU-UKI/KABAG-SDM/001/VI/2026', 'tanggal' => '2026-06-03', 'penerima' => 'Badan Kepegawaian Negara', 'perihal' => 'Pengiriman Data PNS untuk Verifikasi', 'sifat' => 'penting', 'status' => 'approved'],
    ['nomor_surat_otomatis' => 'SK/RSU-UKI/KABAG-KEU/001/VI/2026', 'tanggal' => '2026-06-04', 'penerima' => 'Bank Mandiri Cabang Cawang', 'perihal' => 'Pengajuan Kredit Modal Kerja untuk Pengadaan Alkes', 'sifat' => 'rahasia', 'status' => 'draft'],
    ['nomor_surat_otomatis' => 'SK/RSU-UKI/KABAG-PELMED/001/VI/2026', 'tanggal' => '2026-06-04', 'penerima' => 'Distributor Alkes PT Medika Sejahtera', 'perihal' => 'Pemesanan Alat Kesehatan Ruang Operasi', 'sifat' => 'penting', 'status' => 'draft'],
    ['nomor_surat_otomatis' => 'SK/RSU-UKI/KABAG-UMUM/001/VI/2026', 'tanggal' => '2026-06-05', 'penerima' => 'PT Sarana Jaya Properti', 'perihal' => 'Perpanjangan Kontrak Sewa Lahan Parkir', 'sifat' => 'biasa', 'status' => 'approved'],
    ['nomor_surat_otomatis' => 'SK/RSU-UKI/DIR/004/VI/2026', 'tanggal' => '2026-06-05', 'penerima' => 'Kepolisian Resor Jakarta Timur', 'perihal' => 'Pemberitahuan Pelaksanaan Rekruitmen Tenaga Keamanan', 'sifat' => 'biasa', 'status' => 'terkirim'],
];

foreach ($suratKeluar as $i => $s) {
    DB::table('surat_keluar')->insert(array_merge($s, [
        'created_by' => $userId,
        'created_at' => $now->copy()->subDays(7 - $i),
        'updated_at' => $now,
    ]));
}
echo "✅ 10 Surat Keluar inserted\n";

// ====== 10 DISPOSISI ======
$disposisiData = [
    ['surat_masuk_id' => $suratIds[2], 'isi_disposisi' => 'Mohon segera ditindaklanjuti. Siapkan dokumen alat kesehatan yang diminta BPOM. Koordinasikan dengan Kabag Pelayanan Medis.', 'status' => 'pending', 'tanggal_deadline' => '2026-06-10'],
    ['surat_masuk_id' => $suratIds[3], 'isi_disposisi' => 'Kabag SDM diminta segera mengirimkan data update tenaga medis dan non-medis. Saya tunggu laporannya.', 'status' => 'selesai', 'tanggal_deadline' => '2026-06-05'],
    ['surat_masuk_id' => $suratIds[8], 'isi_disposisi' => 'Data PNS sudah lama tidak diupdate. Mohon Kabag SDM verifikasi dan kirimkan ke BKN paling lambat 10 Juni 2026. Jangan ditunda!', 'status' => 'diteruskan', 'tanggal_deadline' => '2026-06-10'],
    ['surat_masuk_id' => $suratIds[5], 'isi_disposisi' => 'Tolong buat laporan bulanan DBD dan ISPA. Data harus akurat. Kerjasama dengan Unit Rekam Medis.', 'status' => 'pending', 'tanggal_deadline' => '2026-06-12'],
    ['surat_masuk_id' => $suratIds[0], 'isi_disposisi' => 'Saya akan hadir. Tolong siapkan bahan presentasi dan data kinerja RSU UKI tahun 2025-2026.', 'status' => 'pending', 'tanggal_deadline' => '2026-06-14'],
    ['surat_masuk_id' => $suratIds[1], 'isi_disposisi' => 'Hasil akreditasi PARIPURNA ini perlu disosialisasikan ke seluruh unit. Buat pengumuman resmi.', 'status' => 'pending', 'tanggal_deadline' => '2026-06-15'],
    ['surat_masuk_id' => $suratIds[4], 'isi_disposisi' => 'Visum et Repertum harus segera dikirimkan. Tolong cek berkas pasien an. Andi Pratama dan kirimkan ke PN Jaktim.', 'status' => 'diteruskan', 'tanggal_deadline' => '2026-06-08'],
    ['surat_masuk_id' => $suratIds[6], 'isi_disposisi' => 'Kabag SDM — tolong pelajari program JKK dan JKM ini. Buat analisis manfaat untuk pegawai kita.', 'status' => 'pending', 'tanggal_deadline' => '2026-06-20'],
    ['surat_masuk_id' => $suratIds[7], 'isi_disposisi' => 'Tolong daftarkan 5 dokter muda kita untuk ikut seminar ini. Biaya ditanggung rumah sakit.', 'status' => 'pending', 'tanggal_deadline' => '2026-06-18'],
    ['surat_masuk_id' => $suratIds[9], 'isi_disposisi' => 'Audit LKPP ini serius! Kabag Keuangan dan Kabag Umum siapkan semua dokumen pengadaan. Laporan ke saya tiap minggu.', 'status' => 'pending', 'tanggal_deadline' => '2026-06-30'],
];

$disposisiIds = [];
foreach ($disposisiData as $i => $d) {
    $id = DB::table('disposisi')->insertGetId(array_merge($d, [
        'dari_user_id' => $userId,
        'created_at' => $now->copy()->subDays(8 - $i),
        'updated_at' => $now,
    ]));
    $disposisiIds[] = $id;
}
echo "✅ 10 Disposisi inserted\n";

echo "\n🎉 SEMUA DATA DUMMY BERHASIL DIBUAT!\n";
echo "   Total: 10 Surat Masuk + 10 Surat Keluar + 10 Disposisi\n";
