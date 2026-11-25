<?php

namespace Tests\Feature;

use App\Enums\StatusBayarGaji;
use App\Enums\StatusPenerimaan;
use App\Enums\StatusProyek;
use App\Models\KaryawanTukang;
use App\Models\Material;
use App\Models\Pengadaan;
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

        $tanggalDiproses = now()->subDays(2)->toDateString();
        $tanggalDibayar = now()->subDay()->toDateString();

        $response = $this->actingAs($pemilik)->post(route('penggajian.store'), [
            'pesanan_borongan_id' => $borongan->id,
            'tanggal_diproses' => $tanggalDiproses,
            'assignments' => [
                [
                    'karyawan_tukang_id' => $tukangA->id,
                    'total_gaji' => 900000,
                    'status_bayar' => StatusBayarGaji::Dibayar->value,
                    'tanggal_dibayar' => $tanggalDibayar,
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

        $gajiA = PenggajianTukang::where('karyawan_tukang_id', $tukangA->id)->firstOrFail();
        $gajiB = PenggajianTukang::where('karyawan_tukang_id', $tukangB->id)->firstOrFail();

        $this->assertSame('Kepala tukang + bonus target', $gajiA->catatan);
        $this->assertSame($tanggalDibayar, $gajiA->tanggal_dibayar?->toDateString());
        $this->assertNull($gajiB->tanggal_dibayar);

        $pdfResponse = $this->actingAs($pemilik)->get(route('penggajian.pdf', $gajiA));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_marking_payroll_paid_sets_tanggal_dibayar_and_locks_status(): void
    {
        $pemilik = User::factory()->pemilik()->create();
        $gaji = PenggajianTukang::factory()->create([
            'status_bayar' => StatusBayarGaji::BelumDibayar,
            'tanggal_dibayar' => null,
        ]);

        // Marking Belum Dibayar -> Dibayar succeeds (past date allowed if owner forgot to record earlier)
        $yesterday = now()->subDay()->toDateString();
        $this->actingAs($pemilik)
            ->patch(route('penggajian.status', $gaji), [
                'status_bayar' => StatusBayarGaji::Dibayar->value,
                'tanggal_dibayar' => $yesterday,
            ])
            ->assertRedirect();

        $gaji->refresh();
        $this->assertSame(StatusBayarGaji::Dibayar, $gaji->status_bayar);
        $this->assertSame($yesterday, $gaji->tanggal_dibayar?->toDateString());

        // Attempting to revert Dibayar -> Belum Dibayar is blocked
        $this->actingAs($pemilik)
            ->patch(route('penggajian.status', $gaji), [
                'status_bayar' => StatusBayarGaji::BelumDibayar->value,
            ])
            ->assertSessionHasErrors('status_bayar');

        $this->assertSame(StatusBayarGaji::Dibayar, $gaji->fresh()->status_bayar);
    }

    public function test_report_only_counts_paid_payroll_within_tanggal_dibayar_range(): void
    {
        $pemilik = User::factory()->pemilik()->create();
        Material::factory()->stokKritis()->create(['nama_barang' => 'Gipsum Kritis Test']);

        // Paid sales & borongan inside period (counted in Pemasukan Lunas)
        TransaksiPenjualan::factory()->create([
            'tanggal' => '2026-11-29 10:00:00',
            'tanggal_lunas' => '2026-12-02',
            'total_bayar' => 450000,
        ]);
        $boronganPaid = PesananBorongan::factory()->lunas('2026-12-04')->create([
            'tanggal_mulai' => '2026-11-25',
            'total_biaya' => 2100000,
        ]);

        // Unpaid sales & borongan (counted in Piutang Belum Lunas, NOT Pemasukan Lunas)
        TransaksiPenjualan::factory()->belumLunas()->create([
            'tanggal' => '2026-12-03 10:00:00',
            'total_bayar' => 300000,
        ]);
        PesananBorongan::factory()->create([
            'tanggal_mulai' => '2026-12-03',
            'total_biaya' => 1200000,
        ]);

        // Paid outside period (excluded from period Pemasukan Lunas)
        PesananBorongan::factory()->lunas('2026-12-20')->create([
            'tanggal_mulai' => '2026-12-03',
            'total_biaya' => 999000,
        ]);

        // Paid inside period (should be included)
        PenggajianTukang::factory()->dibayar('2026-12-05')->for($boronganPaid)->create([
            'tanggal_diproses' => '2026-11-28',
            'total_gaji' => 700000,
        ]);

        // Unpaid inside period (should NOT be counted in paid expense)
        PenggajianTukang::factory()->for($boronganPaid)->create([
            'tanggal_diproses' => '2026-12-05',
            'status_bayar' => StatusBayarGaji::BelumDibayar,
            'tanggal_dibayar' => null,
            'total_gaji' => 500000,
        ]);

        // Paid outside period (should NOT be included)
        PenggajianTukang::factory()->dibayar('2026-12-20')->for($boronganPaid)->create([
            'tanggal_diproses' => '2026-12-05',
            'total_gaji' => 900000,
        ]);

        // Ordered in Nov, received in Dec period (MUST be included in Pengadaan Diterima)
        Pengadaan::factory()->create([
            'tanggal_pesan' => '2026-11-28',
            'tanggal_terima' => '2026-12-04',
            'status_penerimaan' => StatusPenerimaan::Diterima,
            'total_pengadaan' => 1350000,
        ]);

        // Ordered in Dec period, still Dipesan (excluded from Pengadaan Diterima, counted in total dipesan)
        Pengadaan::factory()->create([
            'tanggal_pesan' => '2026-12-03',
            'tanggal_terima' => null,
            'status_penerimaan' => StatusPenerimaan::Dipesan,
            'total_pengadaan' => 400000,
        ]);

        $response = $this->actingAs($pemilik)->get(route('laporan.index', [
            'dari' => '2026-12-01',
            'sampai' => '2026-12-10',
        ]));

        $response->assertOk();
        $response->assertSee('Gipsum Kritis Test');
        $response->assertSee('Rp 2.550.000');
        $response->assertSee('Piutang belum lunas: Rp 1.500.000');
        $response->assertSee('Rp 1.350.000');
        $response->assertSee('Total dipesan (semua): Rp 400.000');
        $response->assertSee('Rp 700.000');
        $response->assertSee('Belum dibayar: Rp 500.000');

        $pdfResponse = $this->actingAs($pemilik)->get(route('laporan.pdf', [
            'dari' => '2026-12-01',
            'sampai' => '2026-12-10',
        ]));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_duplicate_tukang_on_same_borongan_payroll_is_rejected(): void
    {
        $pemilik = User::factory()->pemilik()->create();
        $borongan = PesananBorongan::factory()->create();
        $tukang = KaryawanTukang::factory()->create();

        // Duplicate tukang in same submission
        $this->actingAs($pemilik)->post(route('penggajian.store'), [
            'pesanan_borongan_id' => $borongan->id,
            'tanggal_diproses' => now()->toDateString(),
            'assignments' => [
                [
                    'karyawan_tukang_id' => $tukang->id,
                    'total_gaji' => 500000,
                    'status_bayar' => StatusBayarGaji::BelumDibayar->value,
                ],
                [
                    'karyawan_tukang_id' => $tukang->id,
                    'total_gaji' => 300000,
                    'status_bayar' => StatusBayarGaji::BelumDibayar->value,
                ],
            ],
        ])->assertSessionHasErrors('assignments.1.karyawan_tukang_id');

        // Re-assigning an already recorded tukang for the same borongan
        PenggajianTukang::factory()->for($borongan)->for($tukang)->create();

        $this->actingAs($pemilik)->post(route('penggajian.store'), [
            'pesanan_borongan_id' => $borongan->id,
            'tanggal_diproses' => now()->toDateString(),
            'assignments' => [
                [
                    'karyawan_tukang_id' => $tukang->id,
                    'total_gaji' => 500000,
                    'status_bayar' => StatusBayarGaji::BelumDibayar->value,
                ],
            ],
        ])->assertSessionHasErrors('assignments');
    }

    public function test_payroll_rejects_future_tanggal_diproses_and_future_tanggal_dibayar(): void
    {
        $pemilik = User::factory()->pemilik()->create();
        $borongan = PesananBorongan::factory()->create();
        $tukang = KaryawanTukang::factory()->create();

        $this->actingAs($pemilik)->post(route('penggajian.store'), [
            'pesanan_borongan_id' => $borongan->id,
            'tanggal_diproses' => now()->addDay()->toDateString(),
            'assignments' => [
                [
                    'karyawan_tukang_id' => $tukang->id,
                    'total_gaji' => 500000,
                    'status_bayar' => StatusBayarGaji::Dibayar->value,
                    'tanggal_dibayar' => now()->addDays(2)->toDateString(),
                ],
            ],
        ])->assertSessionHasErrors(['tanggal_diproses', 'assignments.0.tanggal_dibayar']);
    }
}
