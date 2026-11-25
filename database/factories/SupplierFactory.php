<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_supplier' => fake()->unique()->numerify('SUP-###'),
            'nama_supplier' => fake()->company(),
            'no_telepon' => fake()->numerify('08##########'),
            'alamat' => fake()->address(),
            'keterangan' => 'Supplier material gipsum & PVC',
        ];
    }
}
