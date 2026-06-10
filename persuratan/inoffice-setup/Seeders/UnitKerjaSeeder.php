<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitKerjaSeeder extends Seeder
{
    public function run(): void
    {
        // Struktur awal RSU UKI — bisa dikustomisasi via Admin panel
        $units = [
            // Level 1 — Direktur
            ['id' => 1, 'nama' => 'Direktur Utama', 'kode' => 'DIR', 'parent_id' => null, 'level' => 1],

            // Level 2 — Wakil Direktur
            ['id' => 2, 'nama' => 'Wakil Direktur Medis', 'kode' => 'WADIR-MED', 'parent_id' => 1, 'level' => 2],
            ['id' => 3, 'nama' => 'Wakil Direktur Umum & Keuangan', 'kode' => 'WADIR-UK', 'parent_id' => 1, 'level' => 2],

            // Level 3 — Kepala Bagian (di bawah Wadir Medis)
            ['id' => 4, 'nama' => 'Kepala Bagian Pelayanan Medis', 'kode' => 'KABAG-PELMED', 'parent_id' => 2, 'level' => 3],
            ['id' => 5, 'nama' => 'Kepala Bagian Keperawatan', 'kode' => 'KABAG-KEP', 'parent_id' => 2, 'level' => 3],

            // Level 3 — Kepala Bagian (di bawah Wadir UK)
            ['id' => 6, 'nama' => 'Kepala Bagian SDM', 'kode' => 'KABAG-SDM', 'parent_id' => 3, 'level' => 3],
            ['id' => 7, 'nama' => 'Kepala Bagian Keuangan', 'kode' => 'KABAG-KEU', 'parent_id' => 3, 'level' => 3],
            ['id' => 8, 'nama' => 'Kepala Bagian Umum', 'kode' => 'KABAG-UMUM', 'parent_id' => 3, 'level' => 3],

            // Level 4 — Unit (contoh)
            ['id' => 9,  'nama' => 'Unit Rekam Medis', 'kode' => 'UNIT-RM', 'parent_id' => 4, 'level' => 4],
            ['id' => 10, 'nama' => 'Unit IT', 'kode' => 'UNIT-IT', 'parent_id' => 8, 'level' => 4],
        ];

        foreach ($units as $unit) {
            DB::table('unit_kerja')->updateOrInsert(
                ['kode' => $unit['kode']],
                array_merge($unit, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
