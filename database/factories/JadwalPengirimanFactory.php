<?php

namespace Database\Factories;

use App\Enums\StatusPengiriman;
use App\Models\JadwalPengiriman;
use App\Models\PesananBorongan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JadwalPengiriman>
 */
class JadwalPengirimanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_jadwal' => fake()->unique()->numerify('JDW-######'),
            'transaksi_penjualan_id' => null,
            'pesanan_borongan_id' => PesananBorongan::factory(),
            'alamat_pengiriman' => fake()->address(),
            'tanggal_kirim' => now()->addDay()->toDateString(),
            'jam_kirim' => '09:00',
            'biaya_pengiriman' => 50000,
            'status_pengiriman' => StatusPengiriman::Terjadwal,
            'catatan_bentrok' => null,
        ];
    }
}
