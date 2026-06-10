<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TemplateSurat;

class TemplateSuratSeeder extends Seeder
{
    public function run(): void
    {
        TemplateSurat::firstOrCreate(
            ['nama' => 'Surat Keputusan (SK) Direktur'],
            [
                'jenis' => 'surat_keluar',
                'konten_html' => '<h3>SURAT KEPUTUSAN DIREKTUR RSU UKI</h3><p>Nomor: [NOMOR_SURAT]</p><p>Tentang:<br><strong>[TENTANG]</strong></p><p>Menimbang: ...</p><p>Mengingat: ...</p><p>MEMUTUSKAN: ...</p><p>Ditetapkan di: Jakarta<br>Pada tanggal: [TANGGAL]</p><p>Direktur RSU UKI</p><br><br><p>( [NAMA_DIREKTUR] )</p>',
                'is_active' => true,
            ]
        );

        TemplateSurat::firstOrCreate(
            ['nama' => 'Surat Tugas'],
            [
                'jenis' => 'surat_keluar',
                'konten_html' => '<h3>SURAT TUGAS</h3><p>Nomor: [NOMOR_SURAT]</p><p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p><table border="1" style="width:100%;"><tr><td>Nama</td><td>: [NAMA_PEGAWAI]</td></tr><tr><td>Jabatan</td><td>: [JABATAN]</td></tr></table><p>Ditugaskan untuk: [TUGAS]</p><p>Demikian surat tugas ini dibuat untuk dilaksanakan dengan tanggung jawab.</p><p>Jakarta, [TANGGAL]</p><p>Pemberi Tugas,</p><br><br><p>( [NAMA_ATASAN] )</p>',
                'is_active' => true,
            ]
        );

        TemplateSurat::firstOrCreate(
            ['nama' => 'Nota Dinas'],
            [
                'jenis' => 'memo',
                'konten_html' => '<h3>NOTA DINAS</h3><table style="width:100%;"><tr><td>Kepada</td><td>: [KEPADA]</td></tr><tr><td>Dari</td><td>: [DARI]</td></tr><tr><td>Perihal</td><td>: [PERIHAL]</td></tr><tr><td>Tanggal</td><td>: [TANGGAL]</td></tr></table><hr><p>Isi Nota Dinas:</p><p>[ISI_MEMO]</p>',
                'is_active' => true,
            ]
        );
    }
}
