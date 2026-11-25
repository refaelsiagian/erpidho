<?php

namespace Database\Factories;

use App\Enums\MetodeBayar;
use App\Enums\StatusPembayaran;
use App\Enums\StatusProyek;
use App\Models\Pelanggan;
use App\Models\PesananBorongan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PesananBorongan>
 */
class PesananBoronganFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $biayaJasa = fake()->numberBetween(500, 2500) * 1000;

        return [
            'kode_borongan' => fake()->unique()->numerify('BRN-######'),
            'pelanggan_id' => Pelanggan::factory(),
            'user_id' => User::factory(),
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_target' => now()->addDays(7)->toDateString(),
            'lokasi_proyek' => fake()->address(),
            'biaya_jasa' => $biayaJasa,
            'biaya_pengiriman' => 0,
            'total_biaya' => $biayaJasa,
            'metode_bayar' => MetodeBayar::Tunai,
            'status_pembayaran' => StatusPembayaran::BelumLunas,
            'tanggal_lunas' => null,
            'status_proyek' => StatusProyek::Menunggu,
        ];
    }

    public function lunas(?string $tanggalLunas = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_pembayaran' => StatusPembayaran::Lunas,
            'tanggal_lunas' => $tanggalLunas ?? now()->toDateString(),
        ]);
    }

    public function selesai(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_proyek' => StatusProyek::Selesai,
        ]);
    }
}
