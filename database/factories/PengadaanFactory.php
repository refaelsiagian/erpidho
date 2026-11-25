<?php

namespace Database\Factories;

use App\Enums\StatusPenerimaan;
use App\Models\Pengadaan;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengadaan>
 */
class PengadaanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_pengadaan' => fake()->unique()->numerify('PGD-######'),
            'user_id' => User::factory(),
            'supplier_id' => Supplier::factory(),
            'tanggal_pesan' => now()->toDateString(),
            'tanggal_terima' => null,
            'total_pengadaan' => fake()->numberBetween(500, 5000) * 1000,
            'status_penerimaan' => StatusPenerimaan::Dipesan,
        ];
    }
}
