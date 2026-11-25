<?php

namespace Database\Factories;

use App\Enums\KategoriMaterial;
use App\Enums\SatuanMaterial;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hargaBeli = fake()->numberBetween(15, 120) * 1000;

        return [
            'kode_barang' => fake()->unique()->numerify('BRG-####'),
            'nama_barang' => fake()->words(3, true),
            'kategori' => fake()->randomElement(KategoriMaterial::cases()),
            'satuan' => fake()->randomElement(SatuanMaterial::cases()),
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaBeli + (fake()->numberBetween(5, 30) * 1000),
            'stok_sisa' => fake()->numberBetween(10, 100),
            'batas_minimum' => 5,
        ];
    }

    public function stokKritis(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stok_sisa' => 3,
            'batas_minimum' => 5,
        ]);
    }
}
