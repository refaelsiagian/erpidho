<?php

namespace Tests\Feature;

use App\Enums\MetodeBayar;
use App\Enums\StatusPembayaran;
use App\Enums\StatusProyek;
use App\Models\Material;
use App\Models\Pelanggan;
use App\Models\PesananBorongan;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenjualanAndBoronganTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_sale_deducts_stock_calculates_total_and_renders_pdf(): void
    {
        $user = User::factory()->keluarga()->create();
        $material = Material::factory()->create([
            'harga_jual' => 75000,
            'stok_sisa' => 20,
        ]);

        $response = $this->actingAs($user)->post(route('penjualan.store'), [
            'nama_pelanggan_baru' => 'Pak Joko',
            'no_telepon_baru' => '08123456789',
            'perlu_pengiriman' => '1',
            'biaya_pengiriman' => 50000,
            'metode_bayar' => MetodeBayar::Tunai->value,
            'status_pembayaran' => StatusPembayaran::Lunas->value,
            'items' => [
                ['material_id' => $material->id, 'jumlah' => 4],
            ],
        ]);

        $transaksi = TransaksiPenjualan::firstOrFail();

        $response->assertRedirect(route('penjualan.show', $transaksi));
        $this->assertTrue($transaksi->perlu_pengiriman);
        $this->assertSame(50000, $transaksi->biaya_pengiriman);
        $this->assertSame(350000, $transaksi->total_bayar);
        $this->assertSame(16, $material->fresh()->stok_sisa);
        $this->assertDatabaseHas('pelanggans', ['nama_pelanggan' => 'Pak Joko']);

        $pdfResponse = $this->actingAs($user)->get(route('penjualan.pdf', $transaksi));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_direct_sale_fails_when_stock_is_insufficient(): void
    {
        $user = User::factory()->keluarga()->create();
        $material = Material::factory()->create([
            'stok_sisa' => 2,
        ]);

        $response = $this->actingAs($user)->post(route('penjualan.store'), [
            'metode_bayar' => MetodeBayar::Tunai->value,
            'status_pembayaran' => StatusPembayaran::Lunas->value,
            'items' => [
                ['material_id' => $material->id, 'jumlah' => 5],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertSame(2, $material->fresh()->stok_sisa);
        $this->assertDatabaseCount('transaksi_penjualans', 0);
    }

    public function test_borongan_order_deducts_stock_and_includes_manual_biaya_jasa(): void
    {
        $user = User::factory()->keluarga()->create();
        $pelanggan = Pelanggan::factory()->create();
        $material = Material::factory()->create([
            'harga_jual' => 50000,
            'stok_sisa' => 30,
        ]);

        $response = $this->actingAs($user)->post(route('borongan.store'), [
            'pelanggan_id' => $pelanggan->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_target' => now()->addDays(4)->toDateString(),
            'lokasi_proyek' => 'Jl. Setia Budi No. 10',
            'biaya_jasa' => 1500000,
            'biaya_pengiriman' => 75000,
            'metode_bayar' => MetodeBayar::Transfer->value,
            'status_pembayaran' => StatusPembayaran::BelumLunas->value,
            'items' => [
                ['material_id' => $material->id, 'jumlah_material' => 10],
            ],
        ]);

        $borongan = PesananBorongan::firstOrFail();

        $response->assertRedirect(route('borongan.show', $borongan));
        $this->assertSame(75000, $borongan->biaya_pengiriman);
        $this->assertSame(2075000, $borongan->total_biaya);
        $this->assertSame(StatusProyek::Menunggu, $borongan->status_proyek);
        $this->assertSame(StatusPembayaran::BelumLunas, $borongan->status_pembayaran);
        $this->assertNull($borongan->tanggal_lunas);
        $this->assertSame(20, $material->fresh()->stok_sisa);

        $this->actingAs($user)
            ->patch(route('borongan.status', $borongan), ['status_proyek' => StatusProyek::Selesai->value])
            ->assertForbidden();

        $pdfResponse = $this->actingAs($user)->get(route('borongan.pdf', $borongan));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_borongan_status_is_forward_only_and_selesai_is_locked(): void
    {
        $keluarga = User::factory()->keluarga()->create();
        $pemilik = User::factory()->pemilik()->create();
        $borongan = PesananBorongan::factory()->create(['status_proyek' => StatusProyek::Menunggu]);

        // Keluarga can move Menunggu -> Berjalan
        $this->actingAs($keluarga)
            ->patch(route('borongan.status', $borongan), ['status_proyek' => StatusProyek::Berjalan->value])
            ->assertRedirect();
        $this->assertSame(StatusProyek::Berjalan, $borongan->fresh()->status_proyek);

        // Cannot revert Berjalan -> Menunggu
        $this->actingAs($pemilik)
            ->patch(route('borongan.status', $borongan), ['status_proyek' => StatusProyek::Menunggu->value])
            ->assertSessionHasErrors('status_proyek');

        // Pemilik verifies Berjalan -> Selesai
        $this->actingAs($pemilik)
            ->patch(route('borongan.status', $borongan), ['status_proyek' => StatusProyek::Selesai->value])
            ->assertRedirect();
        $this->assertSame(StatusProyek::Selesai, $borongan->fresh()->status_proyek);

        // Neither Keluarga nor Pemilik can revert Selesai
        $this->actingAs($keluarga)
            ->patch(route('borongan.status', $borongan), ['status_proyek' => StatusProyek::Berjalan->value])
            ->assertSessionHasErrors('status_proyek');
        $this->actingAs($pemilik)
            ->patch(route('borongan.status', $borongan), ['status_proyek' => StatusProyek::Berjalan->value])
            ->assertSessionHasErrors('status_proyek');
        $this->assertSame(StatusProyek::Selesai, $borongan->fresh()->status_proyek);
    }

    public function test_only_pemilik_can_revert_lunas_payment_status(): void
    {
        $keluarga = User::factory()->keluarga()->create();
        $pemilik = User::factory()->pemilik()->create();
        $transaksi = TransaksiPenjualan::factory()->create([
            'status_pembayaran' => StatusPembayaran::BelumLunas,
        ]);

        // Keluarga can mark Belum Lunas -> Lunas
        $this->actingAs($keluarga)
            ->patch(route('penjualan.status', $transaksi), ['status_pembayaran' => StatusPembayaran::Lunas->value])
            ->assertRedirect();
        $this->assertSame(StatusPembayaran::Lunas, $transaksi->fresh()->status_pembayaran);

        // Keluarga cannot revert Lunas -> Belum Lunas
        $this->actingAs($keluarga)
            ->patch(route('penjualan.status', $transaksi), ['status_pembayaran' => StatusPembayaran::BelumLunas->value])
            ->assertForbidden();
        $this->assertSame(StatusPembayaran::Lunas, $transaksi->fresh()->status_pembayaran);

        // Pemilik can revert Lunas -> Belum Lunas
        $this->actingAs($pemilik)
            ->patch(route('penjualan.status', $transaksi), ['status_pembayaran' => StatusPembayaran::BelumLunas->value])
            ->assertRedirect();
        $this->assertSame(StatusPembayaran::BelumLunas, $transaksi->fresh()->status_pembayaran);
        $this->assertNull($transaksi->fresh()->tanggal_lunas);
    }

    public function test_borongan_payment_lunas_sets_tanggal_lunas_and_only_pemilik_can_revert(): void
    {
        $keluarga = User::factory()->keluarga()->create();
        $pemilik = User::factory()->pemilik()->create();
        $borongan = PesananBorongan::factory()->create([
            'status_pembayaran' => StatusPembayaran::BelumLunas,
            'tanggal_lunas' => null,
        ]);

        // Keluarga can mark Belum Lunas -> Lunas (including catching up a past payment date)
        $yesterday = now()->subDay()->toDateString();
        $this->actingAs($keluarga)
            ->patch(route('borongan.payment', $borongan), [
                'status_pembayaran' => StatusPembayaran::Lunas->value,
                'tanggal_lunas' => $yesterday,
            ])
            ->assertRedirect();

        $borongan->refresh();
        $this->assertSame(StatusPembayaran::Lunas, $borongan->status_pembayaran);
        $this->assertSame($yesterday, $borongan->tanggal_lunas?->toDateString());

        // Keluarga cannot revert Lunas -> Belum Lunas
        $this->actingAs($keluarga)
            ->patch(route('borongan.payment', $borongan), ['status_pembayaran' => StatusPembayaran::BelumLunas->value])
            ->assertForbidden();
        $this->assertSame(StatusPembayaran::Lunas, $borongan->fresh()->status_pembayaran);

        // Pemilik can revert Lunas -> Belum Lunas
        $this->actingAs($pemilik)
            ->patch(route('borongan.payment', $borongan), ['status_pembayaran' => StatusPembayaran::BelumLunas->value])
            ->assertRedirect();
        $this->assertSame(StatusPembayaran::BelumLunas, $borongan->fresh()->status_pembayaran);
        $this->assertNull($borongan->fresh()->tanggal_lunas);
    }

    public function test_borongan_rejects_past_tanggal_mulai_and_future_tanggal_lunas(): void
    {
        $user = User::factory()->keluarga()->create();
        $pelanggan = Pelanggan::factory()->create();
        $material = Material::factory()->create(['stok_sisa' => 10]);

        $this->actingAs($user)->post(route('borongan.store'), [
            'pelanggan_id' => $pelanggan->id,
            'tanggal_mulai' => now()->subDay()->toDateString(),
            'tanggal_target' => now()->addDays(2)->toDateString(),
            'lokasi_proyek' => 'Jl. Contoh',
            'biaya_jasa' => 500000,
            'biaya_pengiriman' => 0,
            'metode_bayar' => MetodeBayar::Tunai->value,
            'status_pembayaran' => StatusPembayaran::Lunas->value,
            'tanggal_lunas' => now()->addDay()->toDateString(),
            'items' => [
                ['material_id' => $material->id, 'jumlah_material' => 2],
            ],
        ])->assertSessionHasErrors(['tanggal_mulai', 'tanggal_lunas']);
    }
}
