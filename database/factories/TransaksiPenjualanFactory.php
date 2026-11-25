<?php

namespace Database\Factories;

use App\Enums\MetodeBayar;
use App\Enums\StatusPembayaran;
use App\Models\Pelanggan;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransaksiPenjualan>
 */
class TransaksiPenjualanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_transaksi' => fake()->unique()->numerify('TRX-######'),
            'pelanggan_id' => Pelanggan::factory(),
            'user_id' => User::factory(),
            'tanggal' => now(),
            'perlu_pengiriman' => false,
            'biaya_pengiriman' => 0,
            'total_bayar' => fake()->numberBetween(100, 1500) * 1000,
            'metode_bayar' => MetodeBayar::Tunai,
            'status_pembayaran' => StatusPembayaran::Lunas,
            'tanggal_lunas' => now()->toDateString(),
        ];
    }

    public function belumLunas(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_pembayaran' => StatusPembayaran::BelumLunas,
            'tanggal_lunas' => null,
        ]);
    }
}
