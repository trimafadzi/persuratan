<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('is_active', true)->get();
        if ($users->isEmpty()) return;

        $dir = $users->where('username', 'direktur')->first() ?? $users->first();
        $admin = $users->where('username', 'admin')->first() ?? $users->first();

        // Bersihkan data lama agar tidak bentrok (Force delete)
        \DB::table('laporan_disposisi')->delete();
        \DB::table('disposisi_penerima')->delete();
        \DB::table('disposisi')->delete();
        \DB::table('surat_keluar')->delete();
        \DB::table('surat_masuk')->delete();

        // 1. Buat Surat Masuk
        $suratMasuk = [];
        for ($i = 1; $i <= 15; $i++) {
            $tgl = Carbon::now()->subDays(rand(1, 60));
            $sm = SuratMasuk::create([
                'nomor_surat' => '00'.$i.'/Eks/'.$tgl->format('Y'),
                'tanggal_surat' => $tgl->copy()->subDays(2),
                'tanggal_terima' => $tgl,
                'pengirim' => 'Instansi Eksternal ' . rand(1,5),
                'perihal' => 'Undangan / Pemberitahuan ' . $i,
                'sifat' => collect(['biasa','penting','segera'])->random(),
                'ringkasan' => 'Ringkasan isi surat ' . $i,
                'status' => collect(['belum_dibaca','dibaca','didisposisi','selesai'])->random(),
                'created_by' => $admin->id,
                'created_at' => $tgl,
                'updated_at' => $tgl->copy()->addHours(2),
            ]);
            $suratMasuk[] = $sm;
        }

        // 2. Buat Surat Keluar
        for ($i = 1; $i <= 10; $i++) {
            $tgl = Carbon::now()->subDays(rand(1, 60));
            SuratKeluar::create([
                'nomor_surat_otomatis' => 'SK/RSU-UKI/DIR/00'.$i.'/'.$tgl->format('m/Y'),
                'tanggal' => $tgl,
                'penerima' => 'Dinas Kesehatan ' . rand(1,3),
                'perihal' => 'Balasan Laporan Bulanan ' . $i,
                'sifat' => collect(['biasa','penting','rahasia'])->random(),
                'isi' => 'Isi singkat surat keluar ' . $i,
                'status' => 'draft',
                'created_by' => $admin->id,
                'created_at' => $tgl,
                'updated_at' => $tgl,
            ]);
        }

        // 3. Buat Disposisi dari sebagian surat masuk
        foreach (array_slice($suratMasuk, 0, 8) as $sm) {
            $disp = Disposisi::create([
                'surat_masuk_id' => $sm->id,
                'dari_user_id' => $dir->id,
                'isi_disposisi' => 'Mohon ditindaklanjuti segera sesuai aturan yang berlaku.',
                'status' => collect(['pending','diteruskan','selesai'])->random(),
                'tanggal_deadline' => Carbon::now()->addDays(rand(-5, 5)),
                'created_at' => $sm->created_at->addHours(1),
            ]);
            // Attach penerima
            $otherUsers = $users->where('id', '!=', $dir->id);
            if ($otherUsers->isNotEmpty()) {
                $penerima = $otherUsers->random(min(2, $otherUsers->count()));
                foreach ($penerima as $p) {
                    $disp->penerima()->attach($p->id, ['is_read' => rand(0,1)]);
                }
            }
        }
    }
}
