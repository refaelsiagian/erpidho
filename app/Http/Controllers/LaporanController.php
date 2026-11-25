<?php

namespace App\Http\Controllers;

use App\Enums\StatusBayarGaji;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPenerimaan;
use App\Models\Material;
use App\Models\Pengadaan;
use App\Models\PenggajianTukang;
use App\Models\PesananBorongan;
use App\Models\TransaksiPenjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        return view('laporan.index', $this->buildReportData($request));
    }

    public function pdf(Request $request): Response
    {
        $data = $this->buildReportData($request);

        $pdf = Pdf::loadView('pdf.laporan-operasional', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download("laporan-operasional-{$data['dari']}-sd-{$data['sampai']}.pdf");
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportData(Request $request): array
    {
        $dari = $request->string('dari')->trim()->toString() ?: now()->startOfMonth()->toDateString();
        $sampai = $request->string('sampai')->trim()->toString() ?: now()->toDateString();

        $penjualans = TransaksiPenjualan::query()
            ->with('pelanggan')
            ->where('status_pembayaran', StatusPembayaran::Lunas)
            ->whereNotNull('tanggal_lunas')
            ->whereDate('tanggal_lunas', '>=', $dari)
            ->whereDate('tanggal_lunas', '<=', $sampai)
            ->orderByDesc('tanggal_lunas')
            ->get();

        $borongans = PesananBorongan::query()
            ->with('pelanggan')
            ->where('status_pembayaran', StatusPembayaran::Lunas)
            ->whereNotNull('tanggal_lunas')
            ->whereDate('tanggal_lunas', '>=', $dari)
            ->whereDate('tanggal_lunas', '<=', $sampai)
            ->orderByDesc('tanggal_lunas')
            ->get();

        $pengadaans = Pengadaan::query()
            ->with('supplier')
            ->where('status_penerimaan', StatusPenerimaan::Diterima)
            ->whereNotNull('tanggal_terima')
            ->whereDate('tanggal_terima', '>=', $dari)
            ->whereDate('tanggal_terima', '<=', $sampai)
            ->orderByDesc('tanggal_terima')
            ->get();

        $penggajians = PenggajianTukang::query()
            ->with(['karyawanTukang', 'pesananBorongan'])
            ->where('status_bayar', StatusBayarGaji::Dibayar)
            ->whereNotNull('tanggal_dibayar')
            ->whereDate('tanggal_dibayar', '>=', $dari)
            ->whereDate('tanggal_dibayar', '<=', $sampai)
            ->orderByDesc('tanggal_dibayar')
            ->get();

        $materials = Material::query()
            ->orderBy('kode_barang')
            ->get();

        $totalPenjualanLangsung = (int) $penjualans->sum('total_bayar');
        $totalBorongan = (int) $borongans->sum('total_biaya');
        $totalOmzet = $totalPenjualanLangsung + $totalBorongan;

        $totalPiutangBelumLunas = (int) TransaksiPenjualan::query()
            ->where('status_pembayaran', StatusPembayaran::BelumLunas)
            ->sum('total_bayar')
            + (int) PesananBorongan::query()
                ->where('status_pembayaran', StatusPembayaran::BelumLunas)
                ->sum('total_biaya');

        $totalPengadaan = (int) $pengadaans->sum('total_pengadaan');

        $totalPengadaanSemua = (int) Pengadaan::query()
            ->whereDate('tanggal_pesan', '>=', $dari)
            ->whereDate('tanggal_pesan', '<=', $sampai)
            ->sum('total_pengadaan');
        $totalGajiTukang = (int) $penggajians->sum('total_gaji');
        $totalGajiBelumDibayar = (int) PenggajianTukang::query()
            ->where('status_bayar', StatusBayarGaji::BelumDibayar)
            ->sum('total_gaji');

        return [
            'dari' => $dari,
            'sampai' => $sampai,
            'penjualans' => $penjualans,
            'borongans' => $borongans,
            'pengadaans' => $pengadaans,
            'penggajians' => $penggajians,
            'materials' => $materials,
            'totalPenjualanLangsung' => $totalPenjualanLangsung,
            'totalBorongan' => $totalBorongan,
            'totalOmzet' => $totalOmzet,
            'totalPiutangBelumLunas' => $totalPiutangBelumLunas,
            'totalPengadaan' => $totalPengadaan,
            'totalPengadaanSemua' => $totalPengadaanSemua,
            'totalGajiTukang' => $totalGajiTukang,
            'totalGajiBelumDibayar' => $totalGajiBelumDibayar,
        ];
    }
}
