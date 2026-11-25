<?php

namespace Database\Factories;

use App\Enums\StatusKaryawan;
use App\Models\KaryawanTukang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KaryawanTukang>
 */
class KaryawanTukangFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_tukang' => fake()->unique()->numerify('TKG-###'),
            'nama_tukang' => fake()->name(),
            'no_telepon' => fake()->numerify('08##########'),
            'status_karyawan' => StatusKaryawan::Aktif,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_karyawan' => StatusKaryawan::Nonaktif,
        ]);
    }
}
