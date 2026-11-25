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
            'metode_bayar' => MetodeBayar::Tunai->value,
            'status_pembayaran' => StatusPembayaran::Lunas->value,
            'items' => [
                ['material_id' => $material->id, 'jumlah' => 4],
            ],
        ]);

        $transaksi = TransaksiPenjualan::firstOrFail();

        $response->assertRedirect(route('penjualan.show', $transaksi));
        $this->assertSame(300000, $transaksi->total_bayar);
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
            'tanggal_mulai' => '2026-12-01',
            'tanggal_target' => '2026-12-05',
            'lokasi_proyek' => 'Jl. Setia Budi No. 10',
            'biaya_jasa' => 1500000,
            'items' => [
                ['material_id' => $material->id, 'jumlah_material' => 10],
            ],
        ]);

        $borongan = PesananBorongan::firstOrFail();

        $response->assertRedirect(route('borongan.show', $borongan));
        $this->assertSame(2000000, $borongan->total_biaya);
        $this->assertSame(StatusProyek::Menunggu, $borongan->status_proyek);
        $this->assertSame(20, $material->fresh()->stok_sisa);

        $this->actingAs($user)
            ->patch(route('borongan.status', $borongan), ['status_proyek' => StatusProyek::Selesai->value])
            ->assertForbidden();

        $pdfResponse = $this->actingAs($user)->get(route('borongan.pdf', $borongan));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }
}

