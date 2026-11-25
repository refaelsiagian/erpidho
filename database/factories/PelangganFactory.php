<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pelanggan>
 */
class PelangganFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_pelanggan' => fake()->unique()->numerify('PLG-####'),
            'nama_pelanggan' => fake()->name(),
            'no_telepon' => fake()->numerify('08##########'),
            'alamat' => fake()->address(),
        ];
    }
}
