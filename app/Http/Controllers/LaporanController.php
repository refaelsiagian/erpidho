<?php

namespace App\Http\Controllers;

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
            ->whereDate('tanggal', '>=', $dari)
            ->whereDate('tanggal', '<=', $sampai)
            ->orderByDesc('tanggal')
            ->get();

        $borongans = PesananBorongan::query()
            ->with('pelanggan')
            ->whereDate('tanggal_mulai', '>=', $dari)
            ->whereDate('tanggal_mulai', '<=', $sampai)
            ->orderByDesc('tanggal_mulai')
            ->get();

        $pengadaans = Pengadaan::query()
            ->with('supplier')
            ->whereDate('tanggal_pesan', '>=', $dari)
            ->whereDate('tanggal_pesan', '<=', $sampai)
            ->orderByDesc('tanggal_pesan')
            ->get();

        $penggajians = PenggajianTukang::query()
            ->with(['karyawanTukang', 'pesananBorongan'])
            ->whereDate('tanggal_diproses', '>=', $dari)
            ->whereDate('tanggal_diproses', '<=', $sampai)
            ->orderByDesc('tanggal_diproses')
            ->get();

        $materials = Material::query()
            ->orderBy('kode_barang')
            ->get();

        $totalPenjualanLangsung = (int) $penjualans->sum('total_bayar');
        $totalBorongan = (int) $borongans->sum('total_biaya');
        $totalOmzet = $totalPenjualanLangsung + $totalBorongan;

        $totalPengadaan = (int) $pengadaans
            ->where('status_penerimaan', StatusPenerimaan::Diterima)
            ->sum('total_pengadaan');

        $totalPengadaanSemua = (int) $pengadaans->sum('total_pengadaan');
        $totalGajiTukang = (int) $penggajians->sum('total_gaji');

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
            'totalPengadaan' => $totalPengadaan,
            'totalPengadaanSemua' => $totalPengadaanSemua,
            'totalGajiTukang' => $totalGajiTukang,
        ];
    }
}

