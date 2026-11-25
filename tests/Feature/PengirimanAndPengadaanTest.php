<?php

namespace Tests\Feature;

use App\Enums\StatusPenerimaan;
use App\Enums\StatusPengiriman;
use App\Models\JadwalPengiriman;
use App\Models\Material;
use App\Models\Pengadaan;
use App\Models\PesananBorongan;
use App\Models\Supplier;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengirimanAndPengadaanTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_time_delivery_conflict_is_blocked_with_reason(): void
    {
        $user = User::factory()->keluarga()->create();
        $existingBorongan = PesananBorongan::factory()->create(['kode_borongan' => 'BRN-000101']);

        JadwalPengiriman::factory()->create([
            'kode_jadwal' => 'JDW-000101',
            'pesanan_borongan_id' => $existingBorongan->id,
            'tanggal_kirim' => '2026-12-10',
            'jam_kirim' => '09:00',
            'alamat_pengiriman' => 'Jl. Pancing No. 12',
        ]);

        $newBorongan = PesananBorongan::factory()->create();

        $response = $this->actingAs($user)->post(route('pengiriman.store'), [
            'jenis_referensi' => 'borongan',
            'referensi_id' => $newBorongan->id,
            'alamat_pengiriman' => 'Jl. Krakatau No. 5',
            'tanggal_kirim' => '2026-12-10',
            'jam_kirim' => '09:00',
        ]);

        $response->assertSessionHasErrors('jam_kirim');
        $this->assertDatabaseCount('jadwal_pengirimen', 1);
    }

    public function test_one_hour_buffer_shows_warning_then_allows_when_confirmed_without_mutating_order_total(): void
    {
        $user = User::factory()->keluarga()->create();
        JadwalPengiriman::factory()->create([
            'tanggal_kirim' => '2026-12-10',
            'jam_kirim' => '09:00',
        ]);

        $borongan = PesananBorongan::factory()->create([
            'biaya_pengiriman' => 75000,
            'total_biaya' => 1075000,
        ]);

        // First attempt without confirmation triggers buffer warning
        $firstResponse = $this->actingAs($user)->post(route('pengiriman.store'), [
            'jenis_referensi' => 'borongan',
            'referensi_id' => $borongan->id,
            'alamat_pengiriman' => 'Jl. Gatot Subroto',
            'tanggal_kirim' => '2026-12-10',
            'jam_kirim' => '09:45',
        ]);

        $firstResponse->assertSessionHas('buffer_warning');
        $this->assertDatabaseCount('jadwal_pengirimen', 1);

        // Second attempt with confirmation succeeds, mirrors fee, and leaves total_biaya unchanged
        $secondResponse = $this->actingAs($user)->post(route('pengiriman.store'), [
            'jenis_referensi' => 'borongan',
            'referensi_id' => $borongan->id,
            'alamat_pengiriman' => 'Jl. Gatot Subroto',
            'tanggal_kirim' => '2026-12-10',
            'jam_kirim' => '09:45',
            'confirm_buffer_warning' => '1',
        ]);

        $secondResponse->assertRedirect(route('pengiriman.index'));
        $this->assertDatabaseCount('jadwal_pengirimen', 2);
        $this->assertSame(1075000, $borongan->fresh()->total_biaya);
        $this->assertDatabaseHas('jadwal_pengirimen', [
            'pesanan_borongan_id' => $borongan->id,
            'biaya_pengiriman' => 75000,
        ]);
    }

    public function test_pos_sale_without_delivery_option_cannot_be_scheduled(): void
    {
        $user = User::factory()->keluarga()->create();
        $transaksi = TransaksiPenjualan::factory()->create(['perlu_pengiriman' => false]);

        $response = $this->actingAs($user)->post(route('pengiriman.store'), [
            'jenis_referensi' => 'penjualan',
            'referensi_id' => $transaksi->id,
            'alamat_pengiriman' => 'Jl. Asia No. 1',
            'tanggal_kirim' => '2026-12-12',
            'jam_kirim' => '10:00',
        ]);

        $response->assertSessionHasErrors('referensi_id');
        $this->assertDatabaseCount('jadwal_pengirimen', 0);
    }

    public function test_pengadaan_receiving_increments_material_stock(): void
    {
        $user = User::factory()->keluarga()->create();
        $supplier = Supplier::factory()->create();
        $material = Material::factory()->create([
            'stok_sisa' => 4,
            'harga_beli' => 50000,
        ]);

        $this->actingAs($user)->post(route('pengadaan.store'), [
            'supplier_id' => $supplier->id,
            'tanggal_pesan' => now()->toDateString(),
            'items' => [
                [
                    'material_id' => $material->id,
                    'jumlah_pesan' => 20,
                    'harga_beli_satuan' => 52000,
                ],
            ],
        ])->assertRedirect();

        $pengadaan = Pengadaan::firstOrFail();
        $this->assertSame(StatusPenerimaan::Dipesan, $pengadaan->status_penerimaan);
        $this->assertSame(1040000, $pengadaan->total_pengadaan);

        $this->actingAs($user)
            ->post(route('pengadaan.receive', $pengadaan))
            ->assertRedirect();

        $this->assertSame(StatusPenerimaan::Diterima, $pengadaan->fresh()->status_penerimaan);
        $this->assertSame(24, $material->fresh()->stok_sisa);
        $this->assertSame(52000, $material->fresh()->harga_beli);
    }

    public function test_delivery_status_is_forward_only_and_selesai_is_locked(): void
    {
        $user = User::factory()->keluarga()->create();
        $jadwal = JadwalPengiriman::factory()->create([
            'status_pengiriman' => StatusPengiriman::Terjadwal,
        ]);

        // Cannot skip Terjadwal -> Selesai
        $this->actingAs($user)
            ->patch(route('pengiriman.status', $jadwal), ['status_pengiriman' => StatusPengiriman::Selesai->value])
            ->assertSessionHasErrors('status_pengiriman');
        $this->assertSame(StatusPengiriman::Terjadwal, $jadwal->fresh()->status_pengiriman);

        // Terjadwal -> Dikirim succeeds
        $this->actingAs($user)
            ->patch(route('pengiriman.status', $jadwal), ['status_pengiriman' => StatusPengiriman::Dikirim->value])
            ->assertRedirect();
        $this->assertSame(StatusPengiriman::Dikirim, $jadwal->fresh()->status_pengiriman);

        // Dikirim -> Selesai succeeds
        $this->actingAs($user)
            ->patch(route('pengiriman.status', $jadwal), ['status_pengiriman' => StatusPengiriman::Selesai->value])
            ->assertRedirect();
        $this->assertSame(StatusPengiriman::Selesai, $jadwal->fresh()->status_pengiriman);

        // Cannot revert Selesai -> Dikirim or Terjadwal
        $this->actingAs($user)
            ->patch(route('pengiriman.status', $jadwal), ['status_pengiriman' => StatusPengiriman::Dikirim->value])
            ->assertSessionHasErrors('status_pengiriman');
        $this->assertSame(StatusPengiriman::Selesai, $jadwal->fresh()->status_pengiriman);
    }

    public function test_pengadaan_rejects_duplicate_material_lines(): void
    {
        $user = User::factory()->keluarga()->create();
        $supplier = Supplier::factory()->create();
        $material = Material::factory()->create();

        $this->actingAs($user)->post(route('pengadaan.store'), [
            'supplier_id' => $supplier->id,
            'tanggal_pesan' => now()->toDateString(),
            'items' => [
                ['material_id' => $material->id, 'jumlah_pesan' => 5, 'harga_beli_satuan' => 50000],
                ['material_id' => $material->id, 'jumlah_pesan' => 10, 'harga_beli_satuan' => 50000],
            ],
        ])->assertSessionHasErrors('items.1.material_id');

        $this->assertDatabaseCount('pengadaans', 0);
    }

    public function test_delivery_rejects_past_date_or_past_time_today_and_pengadaan_rejects_future_order_date(): void
    {
        $this->travelTo('2026-11-26 14:00:00');

        $user = User::factory()->keluarga()->create();
        $borongan = PesananBorongan::factory()->create();
        $supplier = Supplier::factory()->create();
        $material = Material::factory()->create();

        // Past date rejected
        $this->actingAs($user)->post(route('pengiriman.store'), [
            'jenis_referensi' => 'borongan',
            'referensi_id' => $borongan->id,
            'alamat_pengiriman' => 'Jl. Sudirman',
            'tanggal_kirim' => '2026-11-25',
            'jam_kirim' => '10:00',
        ])->assertSessionHasErrors('tanggal_kirim');

        // Earlier time today rejected
        $this->actingAs($user)->post(route('pengiriman.store'), [
            'jenis_referensi' => 'borongan',
            'referensi_id' => $borongan->id,
            'alamat_pengiriman' => 'Jl. Sudirman',
            'tanggal_kirim' => '2026-11-26',
            'jam_kirim' => '10:00',
        ])->assertSessionHasErrors('jam_kirim');

        // Future tanggal_pesan rejected
        $this->actingAs($user)->post(route('pengadaan.store'), [
            'supplier_id' => $supplier->id,
            'tanggal_pesan' => '2026-11-27',
            'items' => [
                ['material_id' => $material->id, 'jumlah_pesan' => 5, 'harga_beli_satuan' => 50000],
            ],
        ])->assertSessionHasErrors('tanggal_pesan');
    }
}
