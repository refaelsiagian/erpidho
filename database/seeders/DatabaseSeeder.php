<?php

namespace Database\Seeders;

use App\Enums\KategoriMaterial;
use App\Enums\MetodeBayar;
use App\Enums\SatuanMaterial;
use App\Enums\StatusBayarGaji;
use App\Enums\StatusKaryawan;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPenerimaan;
use App\Enums\StatusPengiriman;
use App\Enums\StatusProyek;
use App\Enums\UserRole;
use App\Models\DetailBorongan;
use App\Models\DetailPengadaan;
use App\Models\DetailPenjualan;
use App\Models\JadwalPengiriman;
use App\Models\KaryawanTukang;
use App\Models\Material;
use App\Models\Pelanggan;
use App\Models\Pengadaan;
use App\Models\PenggajianTukang;
use App\Models\PesananBorongan;
use App\Models\Supplier;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with realistic Usaha Ridho Gipsum sample data.
     */
    public function run(): void
    {
        $pemilik = User::create([
            'kode_user' => 'USR-001',
            'nama_lengkap' => 'Ridho Pratama (Pemilik)',
            'username' => 'pemilik',
            'password' => Hash::make('password'),
            'role' => UserRole::Pemilik,
        ]);

        $keluarga = User::create([
            'kode_user' => 'USR-002',
            'nama_lengkap' => 'Siti Aminah (Keluarga)',
            'username' => 'keluarga',
            'password' => Hash::make('password'),
            'role' => UserRole::Keluarga,
        ]);

        $materials = collect([
            [
                'kode_barang' => 'BRG-0001',
                'nama_barang' => 'Papan Gipsum Jayaboard 9mm (120x240)',
                'kategori' => KategoriMaterial::Gipsum,
                'satuan' => SatuanMaterial::Lembar,
                'harga_beli' => 62000,
                'harga_jual' => 78000,
                'stok_sisa' => 45,
                'batas_minimum' => 15,
            ],
            [
                'kode_barang' => 'BRG-0002',
                'nama_barang' => 'Papan Gipsum Elephant 9mm (120x240)',
                'kategori' => KategoriMaterial::Gipsum,
                'satuan' => SatuanMaterial::Lembar,
                'harga_beli' => 58000,
                'harga_jual' => 73000,
                'stok_sisa' => 8,
                'batas_minimum' => 12,
            ],
            [
                'kode_barang' => 'BRG-0003',
                'nama_barang' => 'Plafon PVC Motif Kayu Matte 4m',
                'kategori' => KategoriMaterial::Pvc,
                'satuan' => SatuanMaterial::Lembar,
                'harga_beli' => 48000,
                'harga_jual' => 65000,
                'stok_sisa' => 60,
                'batas_minimum' => 20,
            ],
            [
                'kode_barang' => 'BRG-0004',
                'nama_barang' => 'Plafon PVC Putih Glossy 4m',
                'kategori' => KategoriMaterial::Pvc,
                'satuan' => SatuanMaterial::Lembar,
                'harga_beli' => 44000,
                'harga_jual' => 60000,
                'stok_sisa' => 10,
                'batas_minimum' => 15,
            ],
            [
                'kode_barang' => 'BRG-0005',
                'nama_barang' => 'Rangka Hollow Galvanis 4x4 (4m)',
                'kategori' => KategoriMaterial::Rangka,
                'satuan' => SatuanMaterial::Batang,
                'harga_beli' => 24000,
                'harga_jual' => 33000,
                'stok_sisa' => 75,
                'batas_minimum' => 25,
            ],
            [
                'kode_barang' => 'BRG-0006',
                'nama_barang' => 'Rangka Hollow Galvanis 2x4 (4m)',
                'kategori' => KategoriMaterial::Rangka,
                'satuan' => SatuanMaterial::Batang,
                'harga_beli' => 18000,
                'harga_jual' => 25000,
                'stok_sisa' => 90,
                'batas_minimum' => 25,
            ],
            [
                'kode_barang' => 'BRG-0007',
                'nama_barang' => 'Kompon Gipsum Aplus 20 Kg',
                'kategori' => KategoriMaterial::Aksesoris,
                'satuan' => SatuanMaterial::Sak,
                'harga_beli' => 52000,
                'harga_jual' => 68000,
                'stok_sisa' => 5,
                'batas_minimum' => 8,
            ],
            [
                'kode_barang' => 'BRG-0008',
                'nama_barang' => 'Sekrup Gipsum 6x1 Inch (Isi 1000)',
                'kategori' => KategoriMaterial::Aksesoris,
                'satuan' => SatuanMaterial::Kotak,
                'harga_beli' => 35000,
                'harga_jual' => 48000,
                'stok_sisa' => 22,
                'batas_minimum' => 6,
            ],
        ])->map(fn (array $data): Material => Material::create($data));

        $pelanggans = collect([
            [
                'kode_pelanggan' => 'PLG-0001',
                'nama_pelanggan' => 'Budi Santoso',
                'no_telepon' => '081265001122',
                'alamat' => 'Jl. Merdeka No. 14, Kec. Kota',
            ],
            [
                'kode_pelanggan' => 'PLG-0002',
                'nama_pelanggan' => 'Hendra Wijaya',
                'no_telepon' => '081377889900',
                'alamat' => 'Perum Griya Asri Blok C-7',
            ],
            [
                'kode_pelanggan' => 'PLG-0003',
                'nama_pelanggan' => 'Rina Kartika',
                'no_telepon' => '082144556677',
                'alamat' => 'Jl. Sudirman No. 88',
            ],
        ])->map(fn (array $data): Pelanggan => Pelanggan::create($data));

        $tukangs = collect([
            [
                'kode_tukang' => 'TKG-001',
                'nama_tukang' => 'Agus Supriyanto',
                'no_telepon' => '085211223344',
                'status_karyawan' => StatusKaryawan::Aktif,
            ],
            [
                'kode_tukang' => 'TKG-002',
                'nama_tukang' => 'Dedi Kurniawan',
                'no_telepon' => '085322334455',
                'status_karyawan' => StatusKaryawan::Aktif,
            ],
            [
                'kode_tukang' => 'TKG-003',
                'nama_tukang' => 'Slamet Riyadi',
                'no_telepon' => '085633445566',
                'status_karyawan' => StatusKaryawan::Aktif,
            ],
        ])->map(fn (array $data): KaryawanTukang => KaryawanTukang::create($data));

        $suppliers = collect([
            [
                'kode_supplier' => 'SUP-001',
                'nama_supplier' => 'PT Jaya Material Nusantara',
                'no_telepon' => '0618822334',
                'alamat' => 'Kawasan Industri Medan Tahap II',
                'keterangan' => 'Distributor utama gipsum & kompon',
            ],
            [
                'kode_supplier' => 'SUP-002',
                'nama_supplier' => 'CV Bintang PVC & Baja Ringan',
                'no_telepon' => '0614455667',
                'alamat' => 'Jl. Sisingamangaraja KM 6.5',
                'keterangan' => 'Supplier plafon PVC dan rangka hollow',
            ],
        ])->map(fn (array $data): Supplier => Supplier::create($data));

        // Sample Direct Sale (today)
        $trx = TransaksiPenjualan::create([
            'kode_transaksi' => 'TRX-000001',
            'pelanggan_id' => $pelanggans[0]->id,
            'user_id' => $keluarga->id,
            'tanggal' => now()->subHours(2),
            'biaya_pengiriman' => 0,
            'total_bayar' => (5 * 78000) + (10 * 33000),
            'metode_bayar' => MetodeBayar::Tunai,
            'status_pembayaran' => StatusPembayaran::Lunas,
        ]);

        DetailPenjualan::create([
            'transaksi_penjualan_id' => $trx->id,
            'material_id' => $materials[0]->id,
            'jumlah' => 5,
            'harga_satuan' => 78000,
            'subtotal' => 390000,
        ]);

        DetailPenjualan::create([
            'transaksi_penjualan_id' => $trx->id,
            'material_id' => $materials[4]->id,
            'jumlah' => 10,
            'harga_satuan' => 33000,
            'subtotal' => 330000,
        ]);

        // Sample Completed Borongan + Payroll
        $boronganSelesai = PesananBorongan::create([
            'kode_borongan' => 'BRN-000001',
            'pelanggan_id' => $pelanggans[1]->id,
            'user_id' => $pemilik->id,
            'tanggal_mulai' => now()->subDays(5)->toDateString(),
            'tanggal_target' => now()->subDays(1)->toDateString(),
            'lokasi_proyek' => 'Perum Griya Asri Blok C-7 (Ruang Tamu & Dapur)',
            'biaya_jasa' => 1800000,
            'biaya_pengiriman' => 50000,
            'total_biaya' => (20 * 65000) + (25 * 25000) + 1800000 + 50000,
            'status_proyek' => StatusProyek::Selesai,
        ]);

        DetailBorongan::create([
            'pesanan_borongan_id' => $boronganSelesai->id,
            'material_id' => $materials[2]->id,
            'jumlah_material' => 20,
            'harga_satuan' => 65000,
            'subtotal_material' => 1300000,
        ]);

        DetailBorongan::create([
            'pesanan_borongan_id' => $boronganSelesai->id,
            'material_id' => $materials[5]->id,
            'jumlah_material' => 25,
            'harga_satuan' => 25000,
            'subtotal_material' => 625000,
        ]);

        PenggajianTukang::create([
            'kode_gaji' => 'GAJ-000001',
            'karyawan_tukang_id' => $tukangs[0]->id,
            'pesanan_borongan_id' => $boronganSelesai->id,
            'tanggal_diproses' => now()->subDay()->toDateString(),
            'total_gaji' => 850000,
            'status_bayar' => StatusBayarGaji::Dibayar,
            'catatan' => 'Pemasangan rapi, termasuk lembur 1 hari.',
        ]);

        PenggajianTukang::create([
            'kode_gaji' => 'GAJ-000002',
            'karyawan_tukang_id' => $tukangs[1]->id,
            'pesanan_borongan_id' => $boronganSelesai->id,
            'tanggal_diproses' => now()->subDay()->toDateString(),
            'total_gaji' => 650000,
            'status_bayar' => StatusBayarGaji::BelumDibayar,
            'catatan' => 'Pembayaran menyusul akhir minggu.',
        ]);

        // Sample Active Borongan + Delivery Schedule
        $boronganBerjalan = PesananBorongan::create([
            'kode_borongan' => 'BRN-000002',
            'pelanggan_id' => $pelanggans[2]->id,
            'user_id' => $keluarga->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_target' => now()->addDays(4)->toDateString(),
            'lokasi_proyek' => 'Jl. Sudirman No. 88 (Ruko Lantai 2)',
            'biaya_jasa' => 1200000,
            'biaya_pengiriman' => 75000,
            'total_biaya' => (15 * 78000) + 1200000 + 75000,
            'status_proyek' => StatusProyek::Berjalan,
        ]);

        DetailBorongan::create([
            'pesanan_borongan_id' => $boronganBerjalan->id,
            'material_id' => $materials[0]->id,
            'jumlah_material' => 15,
            'harga_satuan' => 78000,
            'subtotal_material' => 1170000,
        ]);

        JadwalPengiriman::create([
            'kode_jadwal' => 'JDW-000001',
            'transaksi_penjualan_id' => null,
            'pesanan_borongan_id' => $boronganBerjalan->id,
            'alamat_pengiriman' => 'Jl. Sudirman No. 88 (Ruko Lantai 2)',
            'tanggal_kirim' => now()->addDay()->toDateString(),
            'jam_kirim' => '09:00',
            'biaya_pengiriman' => 75000,
            'status_pengiriman' => StatusPengiriman::Terjadwal,
        ]);

        // Sample Procurement
        $pengadaan = Pengadaan::create([
            'kode_pengadaan' => 'PGD-000001',
            'user_id' => $pemilik->id,
            'supplier_id' => $suppliers[0]->id,
            'tanggal_pesan' => now()->toDateString(),
            'tanggal_terima' => null,
            'total_pengadaan' => (20 * 58000) + (10 * 52000),
            'status_penerimaan' => StatusPenerimaan::Dipesan,
        ]);

        DetailPengadaan::create([
            'pengadaan_id' => $pengadaan->id,
            'material_id' => $materials[1]->id,
            'jumlah_pesan' => 20,
            'harga_beli_satuan' => 58000,
            'subtotal' => 1160000,
        ]);

        DetailPengadaan::create([
            'pengadaan_id' => $pengadaan->id,
            'material_id' => $materials[6]->id,
            'jumlah_pesan' => 10,
            'harga_beli_satuan' => 52000,
            'subtotal' => 520000,
        ]);
    }
}
