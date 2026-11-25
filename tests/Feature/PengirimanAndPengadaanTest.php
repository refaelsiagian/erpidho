<?php

namespace Tests\Feature;

use App\Enums\StatusPenerimaan;
use App\Models\JadwalPengiriman;
use App\Models\Material;
use App\Models\Pengadaan;
use App\Models\PesananBorongan;
use App\Models\Supplier;
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
            'biaya_pengiriman' => 50000,
        ]);

        $response->assertSessionHasErrors('jam_kirim');
        $this->assertDatabaseCount('jadwal_pengirimen', 1);
    }

    public function test_one_hour_buffer_shows_warning_then_allows_when_confirmed_and_adds_fee(): void
    {
        $user = User::factory()->keluarga()->create();
        JadwalPengiriman::factory()->create([
            'tanggal_kirim' => '2026-12-10',
            'jam_kirim' => '09:00',
        ]);

        $borongan = PesananBorongan::factory()->create([
            'biaya_pengiriman' => 0,
            'total_biaya' => 1000000,
        ]);

        // First attempt without confirmation triggers buffer warning
        $firstResponse = $this->actingAs($user)->post(route('pengiriman.store'), [
            'jenis_referensi' => 'borongan',
            'referensi_id' => $borongan->id,
            'alamat_pengiriman' => 'Jl. Gatot Subroto',
            'tanggal_kirim' => '2026-12-10',
            'jam_kirim' => '09:45',
            'biaya_pengiriman' => 75000,
        ]);

        $firstResponse->assertSessionHas('buffer_warning');
        $this->assertDatabaseCount('jadwal_pengirimen', 1);

        // Second attempt with confirmation succeeds and adds fee to borongan total
        $secondResponse = $this->actingAs($user)->post(route('pengiriman.store'), [
            'jenis_referensi' => 'borongan',
            'referensi_id' => $borongan->id,
            'alamat_pengiriman' => 'Jl. Gatot Subroto',
            'tanggal_kirim' => '2026-12-10',
            'jam_kirim' => '09:45',
            'biaya_pengiriman' => 75000,
            'confirm_buffer_warning' => '1',
        ]);

        $secondResponse->assertRedirect(route('pengiriman.index'));
        $this->assertDatabaseCount('jadwal_pengirimen', 2);
        $this->assertSame(1075000, $borongan->fresh()->total_biaya);
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
            'tanggal_pesan' => '2026-12-01',
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
}

