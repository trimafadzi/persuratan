<?php

namespace Database\Factories;

use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Disposisi>
 */
class DisposisiFactory extends Factory
{
    protected $model = Disposisi::class;

    public function definition(): array
    {
        $statuses = ['pending', 'diteruskan', 'selesai', 'dibatalkan'];

        return [
            'surat_masuk_id'      => SuratMasuk::factory(),
            'dari_user_id'        => User::factory(),
            'isi_disposisi'       => fake()->sentence(10),
            'status'              => fake()->randomElement($statuses),
            'tanggal_deadline'    => fake()->optional()->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
            'parent_disposisi_id' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function selesai(): static
    {
        return $this->state(['status' => 'selesai']);
    }
}
