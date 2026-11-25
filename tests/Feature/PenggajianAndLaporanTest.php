<?php

namespace Tests\Feature;

use App\Enums\StatusBayarGaji;
use App\Enums\StatusProyek;
use App\Models\KaryawanTukang;
use App\Models\Material;
use App\Models\PenggajianTukang;
use App\Models\PesananBorongan;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenggajianAndLaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemilik_can_verify_project_assign_tukang_manually_with_catatan_and_download_slip(): void
    {
        $pemilik = User::factory()->pemilik()->create();
        $borongan = PesananBorongan::factory()->create(['status_proyek' => StatusProyek::Berjalan]);
        $tukangA = KaryawanTukang::factory()->create();
        $tukangB = KaryawanTukang::factory()->create();

        $response = $this->actingAs($pemilik)->post(route('penggajian.store'), [
            'pesanan_borongan_id' => $borongan->id,
            'tanggal_diproses' => '2026-12-05',
            'assignments' => [
                [
                    'karyawan_tukang_id' => $tukangA->id,
                    'total_gaji' => 900000,
                    'status_bayar' => StatusBayarGaji::Dibayar->value,
                    'catatan' => 'Kepala tukang + bonus target',
                ],
                [
                    'karyawan_tukang_id' => $tukangB->id,
                    'total_gaji' => 650000,
                    'status_bayar' => StatusBayarGaji::BelumDibayar->value,
                    'catatan' => null,
                ],
            ],
        ]);

        $response->assertRedirect(route('penggajian.index'));
        $this->assertSame(StatusProyek::Selesai, $borongan->fresh()->status_proyek);
        $this->assertDatabaseCount('penggajian_tukangs', 2);

        $gaji = PenggajianTukang::where('karyawan_tukang_id', $tukangA->id)->firstOrFail();
        $this->assertSame('Kepala tukang + bonus target', $gaji->catatan);

        $pdfResponse = $this->actingAs($pemilik)->get(route('penggajian.pdf', $gaji));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_pemilik_can_view_and_export_operational_report(): void
    {
        $pemilik = User::factory()->pemilik()->create();
        Material::factory()->stokKritis()->create(['nama_barang' => 'Gipsum Kritis Test']);
        TransaksiPenjualan::factory()->create(['tanggal' => '2026-12-02 10:00:00', 'total_bayar' => 450000]);
        PesananBorongan::factory()->create(['tanggal_mulai' => '2026-12-03', 'total_biaya' => 2100000]);

        $response = $this->actingAs($pemilik)->get(route('laporan.index', [
            'dari' => '2026-12-01',
            'sampai' => '2026-12-10',
        ]));

        $response->assertOk();
        $response->assertSee('Gipsum Kritis Test');
        $response->assertSee('Rp 2.550.000');

        $pdfResponse = $this->actingAs($pemilik)->get(route('laporan.pdf', [
            'dari' => '2026-12-01',
            'sampai' => '2026-12-10',
        ]));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }
}

