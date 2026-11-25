<?php

namespace Database\Factories;

use App\Enums\StatusBayarGaji;
use App\Models\KaryawanTukang;
use App\Models\PenggajianTukang;
use App\Models\PesananBorongan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PenggajianTukang>
 */
class PenggajianTukangFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_gaji' => fake()->unique()->numerify('GAJ-######'),
            'karyawan_tukang_id' => KaryawanTukang::factory(),
            'pesanan_borongan_id' => PesananBorongan::factory()->selesai(),
            'tanggal_diproses' => now()->toDateString(),
            'total_gaji' => fake()->numberBetween(300, 1200) * 1000,
            'status_bayar' => StatusBayarGaji::BelumDibayar,
            'catatan' => null,
        ];
    }
}
