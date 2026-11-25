<?php

namespace App\Http\Controllers;

use App\Enums\StatusBayarGaji;
use App\Enums\StatusPenerimaan;
use App\Enums\StatusPengiriman;
use App\Enums\StatusProyek;
use App\Models\JadwalPengiriman;
use App\Models\Material;
use App\Models\Pengadaan;
use App\Models\PenggajianTukang;
use App\Models\PesananBorongan;
use App\Models\TransaksiPenjualan;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $penjualanHariIni = (int) TransaksiPenjualan::query()
            ->whereDate('tanggal', $today)
            ->sum('total_bayar');

        $boronganHariIni = (int) PesananBorongan::query()
            ->whereDate('tanggal_mulai', $today)
            ->sum('total_biaya');

        $stokKritisMaterials = Material::query()
            ->stokKritis()
            ->orderBy('stok_sisa')
            ->get();

        $jadwalMendatang = JadwalPengiriman::query()
            ->with(['transaksiPenjualan.pelanggan', 'pesananBorongan.pelanggan'])
            ->whereIn('status_pengiriman', [StatusPengiriman::Terjadwal, StatusPengiriman::Dikirim])
            ->orderBy('tanggal_kirim')
            ->orderBy('jam_kirim')
            ->limit(5)
            ->get();

        $boronganAktifCount = PesananBorongan::query()
            ->whereIn('status_proyek', [StatusProyek::Menunggu, StatusProyek::Berjalan])
            ->count();

        $gajiBelumDibayar = (int) PenggajianTukang::query()
            ->where('status_bayar', StatusBayarGaji::BelumDibayar)
            ->sum('total_gaji');

        $pengadaanDipesanCount = Pengadaan::query()
            ->where('status_penerimaan', StatusPenerimaan::Dipesan)
            ->count();

        $transaksiTerbaru = TransaksiPenjualan::query()
            ->with('pelanggan')
            ->latest('tanggal')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'omzetHariIni' => $penjualanHariIni + $boronganHariIni,
            'penjualanHariIni' => $penjualanHariIni,
            'boronganHariIni' => $boronganHariIni,
            'stokKritisMaterials' => $stokKritisMaterials,
            'jadwalMendatang' => $jadwalMendatang,
            'boronganAktifCount' => $boronganAktifCount,
            'gajiBelumDibayar' => $gajiBelumDibayar,
            'pengadaanDipesanCount' => $pengadaanDipesanCount,
            'transaksiTerbaru' => $transaksiTerbaru,
        ]);
    }
}

