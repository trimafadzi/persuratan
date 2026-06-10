<?php

namespace Database\Factories;

use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SuratMasuk>
 */
class SuratMasukFactory extends Factory
{
    protected $model = SuratMasuk::class;

    public function definition(): array
    {
        $statuses = ['belum_dibaca', 'dibaca', 'didisposisi', 'selesai'];
        $sifat    = ['biasa', 'penting', 'rahasia', 'segera'];

        return [
            'nomor_surat'    => 'SM/' . fake()->unique()->numerify('###') . '/' . now()->format('Y'),
            'tanggal_surat'  => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'tanggal_terima' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'pengirim'       => fake()->company(),
            'perihal'        => fake()->sentence(6),
            'sifat'          => fake()->randomElement($sifat),
            'ringkasan'      => fake()->optional()->paragraph(),
            'status'         => fake()->randomElement($statuses),
            'file_path'      => null,
            'unit_kerja_id'  => null,
            'created_by'     => User::factory(),
        ];
    }

    public function belumDibaca(): static
    {
        return $this->state(['status' => 'belum_dibaca']);
    }

    public function selesai(): static
    {
        return $this->state(['status' => 'selesai']);
    }
}
