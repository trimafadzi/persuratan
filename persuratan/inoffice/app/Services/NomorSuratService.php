<?php

namespace App\Services;

use App\Models\NomorSuratCounter;
use Illuminate\Support\Facades\DB;

class NomorSuratService
{
    /**
     * Generate nomor surat keluar otomatis.
     * Format: SK/RSU-UKI/{unit_kode}/{nomor}/{bulan_romawi}/{tahun}
     * Contoh: SK/RSU-UKI/DIR/001/VI/2026
     */
    public function generateNomorSuratKeluar(string $unitKode = 'RSU'): string
    {
        return DB::transaction(function () use ($unitKode) {
            $tahun = now()->year;
            $jenis = 'SK';

            $counter = DB::table('nomor_surat_counter')
                ->where('tahun', $tahun)
                ->where('jenis', $jenis)
                ->lockForUpdate()
                ->first();

            if ($counter) {
                $newCounter = $counter->counter + 1;
                DB::table('nomor_surat_counter')
                    ->where('id', $counter->id)
                    ->update(['counter' => $newCounter, 'updated_at' => now()]);
            } else {
                $newCounter = 1;
                DB::table('nomor_surat_counter')->insert([
                    'tahun' => $tahun,
                    'jenis' => $jenis,
                    'counter' => $newCounter,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $nomorUrut  = str_pad($newCounter, 3, '0', STR_PAD_LEFT);
            $bulanRomawi = $this->toRoman(now()->month);

            return "SK/RSU-UKI/{$unitKode}/{$nomorUrut}/{$bulanRomawi}/{$tahun}";
        });
    }

    private function toRoman(int $month): string
    {
        $romans = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        return $romans[$month - 1] ?? (string)$month;
    }
}
