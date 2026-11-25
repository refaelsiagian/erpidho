# DRAFT BAB II & BAB III (VERSI AKHIR TERBARU) — ERPIDHO
*(Diperbarui penuh agar 100% selaras dengan sistem web Laravel ERPIDHO aktual, perbaikan DFD Level 0–2, dan skema basis data portabel SQLite / Supabase PostgreSQL)*

---

# BAB II
# ANALISIS KEBUTUHAN SISTEM

## 2.1 Requirement Gathering Technique
Dalam mengidentifikasi dan mengumpulkan kebutuhan sistem pada *Usaha Ridho Gipsum*, digunakan dua kombinasi teknik utama:

1. **Wawancara Terstruktur & Mendalam (*In-depth Interview*)**:
   - **Responden**: Pemilik *Usaha Ridho Gipsum* dan anggota keluarga selaku pengelola operasional toko.
   - **Fokus**: Menggali profil bisnis, proses transaksi penjualan langsung dan borongan, kendala toko fisik yang sering tutup sementara saat pengantaran bahan/tukang, mekanisme pembayaran upah tukang di lapangan, serta kebutuhan kendali pelunasan dan laporan kas.
2. **Analisis Dokumen & Artefak (*Document Analysis*)**:
   - **Fokus**: Memeriksa dokumen manual yang digunakan saat ini, seperti nota penjualan kertas, buku rekap stok, catatan pesanan ke *supplier*, dan catatan penggajian tukang untuk ditransformasikan ke dalam struktur data basis data sistem baru.

---

## 2.2 Analisis Proses Bisnis dan Area Fungsional Enterprise
Sistem ERPIDHO mencakup 5 area fungsional utama berikut:

1. **Area Penjualan & Pelayanan (*Sales & POS*)**:
   - **Proses Bisnis**: Memproses dua kategori transaksi secara terpisah: (a) **Penjualan Langsung (POS)** untuk pembelian material eceran (dengan opsi tambahan *Perlu Pengiriman* dan biaya kirim langsung saat *checkout*), dan (b) **Pesanan Paket Borongan** (alokasi material beserta `biaya_jasa` pemasangan dan `biaya_pengiriman` yang diinput manual). Keduanya mencatat metode pembayaran, status pelunasan (`Lunas`/`Belum Lunas`), dan tanggal pelunasan (`tanggal_lunas`).
2. **Area Persediaan & Gudang (*Inventory Management*)**:
   - **Proses Bisnis**: Memotong stok material secara otomatis dan atomik saat transaksi penjualan/borongan dibuat, memantau sisa stok secara *real-time*, serta memicu peringatan dini apabila `stok_sisa <= batas_minimum` (*reorder point*).
3. **Area Pengadaan (*Procurement*)**:
   - **Proses Bisnis**: Mengelola data master **Supplier** (`SUP-XXX`), mencatat pesanan pengadaan bahan baku ke *supplier* terdaftar (dengan pintasan pengisian otomatis item stok kritis), dan menambah stok secara otomatis beserta pembaruan harga beli saat barang dikonfirmasi diterima (`Diterima`).
4. **Area Penggajian Karyawan Tukang (*Payroll & Workforce Management*)**:
   - **Proses Bisnis**: Mengelola data master **Karyawan Tukang** (`TKG-XXX`), memverifikasi proyek borongan yang telah selesai (`Selesai`), menugaskan tukang secara langsung saat penggajian dengan nominal upah manual (`total_gaji`), catatan opsional pemilik (`catatan`), serta mencatat tanggal pembayaran kas (`tanggal_dibayar`).
5. **Area Penjadwalan & Pengiriman (*Scheduling & Delivery*)**:
   - **Proses Bisnis**: Mengelola jadwal logistik pengantaran material untuk pesanan borongan maupun penjualan langsung yang memilih pengiriman, mencegah bentrokan jadwal melalui validasi dua tingkat (blokir tepat waktu & peringatan konfirmasi selisih ≤ 1 jam), serta memantau status pengantaran secara bertahap (`Terjadwal → Dikirim → Selesai`) tanpa mengubah total tagihan pesanan.

---

## 2.3 Identifikasi Persona

### 2.3.1 Identifikasi Stakeholder / Partisipan Proyek

**Tabel 1. Partisipan Proyek**

| No | Jenis Partisipan | Fungsi Kritis / Tanggung Jawab |
| :---: | :--- | :--- |
| 1 | *Project Sponsor* | Pemilik *Usaha Ridho Gipsum*; memberikan otorisasi proyek, menyetujui cakupan (*scope*), dan menjadi pengambil keputusan bisnis utama. |
| 2 | *End-Users* (Pengelola: `Pemilik` & `Keluarga`) | **`Pemilik`** memiliki akses penuh termasuk master sensitif (Material, Tukang, Supplier, Pengguna), verifikasi proyek `Selesai`, penggajian tukang, pembatalan status `Lunas`, dan laporan operasional. **`Keluarga`** mengoperasikan kasir (POS), pesanan borongan, data pelanggan, penjadwalan pengiriman, dan pengadaan stok harian. |
| 3 | *Karyawan Tukang* | Tenaga kerja fisik instalasi gipsum/PVC; melaksanakan pengerjaan proyek di lapangan, melaporkan penyelesaian pekerjaan kepada pemilik, dan menerima slip gaji. *(Tidak memiliki akun login sistem).* |
| 4 | *Customers* (Pelanggan) | Pembeli material langsung di toko maupun pemesan paket instalasi borongan. *(Tidak memiliki akun login sistem; dilayani melalui kasir/pengelola).* |
| 5 | *Project Manager* | Ketua Tim Pengembang; mengelola alur pengerjaan proyek, jadwal *milestone*, dan koordinasi dengan mitra. |
| 6 | *Project Team* | Anggota Tim Pengembang; melaksanakan analisis sistem, perancangan basis data, pengodean aplikasi web, dan pengujian. |

### 2.3.2 Identifikasi Event
* **External Event (Kejadian Eksternal)**:
  1. Pelanggan melakukan pembelian material langsung di toko (*Direct Order*, dengan atau tanpa opsi antar).
  2. Pelanggan memesan paket borongan pemasangan gipsum/PVC.
  3. Pelanggan melunasi tagihan tertunggak (`Belum Lunas → Lunas`).
  4. *Supplier* mengirimkan bahan baku pesanan toko sehingga pengadaan dikonfirmasi diterima (`Dipesan → Diterima`).
  5. Karyawan tukang melaporkan penyelesaian pengerjaan proyek di lapangan kepada pemilik untuk diverifikasi dan diproses gajinya.
* **Temporal Event (Kejadian Berdasarkan Waktu)**:
  1. Pemantauan harian (*Dashboard*): Sistem menghitung total **Pemasukan Lunas Hari Ini** berdasarkan `tanggal_lunas = hari ini`, piutang belum lunas, dan gaji belum dibayar.
  2. Setiap periode laporan (`dari`–`sampai`): Sistem merekapitulasi **Pemasukan Lunas** (`tanggal_lunas`), **Pengadaan Diterima** (`tanggal_terima`), dan **Pengeluaran Gaji Dibayar** (`tanggal_dibayar`).
  3. Validasi kalender saat input: Jadwal pengiriman (`tanggal_kirim`, `jam_kirim`) dan `tanggal_mulai` borongan tidak boleh di masa lalu; sedangkan tanggal pencatatan historis (`tanggal_lunas`, `tanggal_dibayar`, `tanggal_diproses`, `tanggal_pesan`) mengizinkan tanggal lampau namun dibatasi `<= hari ini`.
* **State Event (Kejadian Berdasarkan Kondisi Sistem)**:
  1. Stok fisik material gipsum/PVC menyentuh atau berada di bawah batas minimum (`stok_sisa <= batas_minimum`), sehingga sistem memunculkan peringatan dini (*Notif Stok Minimum*) pada *sidebar*, *Dashboard*, dan halaman Pengadaan.
  2. Terjadi bentrokan jadwal pengiriman aktif pada tanggal yang sama:
     - **Tepat waktu** (`jam_kirim` sama persis) → Sistem **memblokir** pembuatan jadwal disertai rincian jadwal bentrok.
     - **Selisih ≤ 1 jam** → Sistem memunculkan **peringatan konfirmasi** dan mencatat alasan pada `catatan_bentrok` apabila pengelola tetap mengonfirmasi.

---
---

# BAB III
# PERANCANGAN DATA

## 3.1 Kebutuhan Fungsionalitas

**Tabel 2. Kebutuhan Fungsional**

| Kode | Nama Kebutuhan Fungsional | Deskripsi Fungsi Sistem | Pengguna Utama |
| :--- | :--- | :--- | :--- |
| **FR-01** | **Mengelola Autentikasi & Data Master** *(Proses 1.0)* | Autentikasi `username` dan kata sandi dengan pembatasan hak akses (`Pemilik` penuh; `Keluarga` hanya melihat daftar Material dan mengelola Pelanggan). Mencakup CRUD data master *Material*, *Pelanggan*, *Karyawan Tukang*, *Supplier*, dan *Pengguna*. | `Pemilik` (Penuh) & `Keluarga` (Pelanggan & lihat Material) |
| **FR-02** | **Mengelola Penjualan & Pesanan Borongan** *(Proses 2.0)* | Mencatat transaksi pembelian langsung (`TRX-XXXXXX`, opsional *perlu pengiriman* + ongkir) dan pesanan paket borongan (`BRN-XXXXXX`, termasuk `biaya_jasa`, `biaya_pengiriman`, `metode_bayar`, `status_pembayaran`, `tanggal_lunas`), memotong stok atomik, mengontrol status maju proyek (`Menunggu → Berjalan → Selesai`), membatasi pembatalan `Lunas` khusus `Pemilik`, serta menerbitkan Nota PDF. | `Pemilik` & `Keluarga` *(Verifikasi `Selesai` & batal `Lunas` khusus `Pemilik`)* |
| **FR-03** | **Mengelola Penjadwalan Pengiriman** *(Proses 3.0)* | Mencatat jadwal kirim (`JDW-XXXXXX`) untuk pesanan borongan atau POS beropsi antar tanpa mengubah total tagihan pesanan, memblokir bentrok waktu persis, memberi peringatan konfirmasi selisih ≤ 1 jam, melarang jadwal masa lalu, serta mengontrol status maju (`Terjadwal → Dikirim → Selesai`). | `Pemilik` & `Keluarga` |
| **FR-04** | **Mengelola Verifikasi Proyek & Penggajian** *(Proses 4.0)* | Memverifikasi proyek borongan menjadi `Selesai`, menugaskan satu atau beberapa tukang langsung pada penggajian (`GAJ-XXXXXX`) dengan `total_gaji` manual, `catatan` opsional pemilik, dan `tanggal_dibayar`, mencegah duplikasi tukang pada proyek yang sama, mengunci status `Dibayar` satu arah, serta menerbitkan Slip Gaji PDF. | `Pemilik` |
| **FR-05** | **Mengelola Pengadaan & Pemantauan Stok** *(Proses 5.0)* | Memantau stok secara *real-time*, menampilkan peringatan dini stok minimum beserta tombol pengadaan otomatis item kritis, mencatat pesanan pengadaan (`PGD-XXXXXX`) ke master `Supplier`, serta menambah stok & memperbarui harga beli saat barang dikonfirmasi `Diterima` + ekspor PDF daftar pesanan. | `Pemilik` & `Keluarga` |
| **FR-06** | **Mengelola Pelaporan Operasional** *(Proses 6.0)* | Merekapitulasi data berbasis kas dan realisasi berdasarkan rentang tanggal: Pemasukan Lunas (`tanggal_lunas`), Pengadaan Diterima (`tanggal_terima`), Pengeluaran Gaji Dibayar (`tanggal_dibayar`), Piutang Belum Lunas, Gaji Belum Dibayar, dan Laporan Sisa Stok Material dalam tampilan web maupun ekspor PDF. | `Pemilik` |

---

## 3.2 Pemodelan Proses dan Aliran Data

### 3.2.1 Diagram Konteks (DFD Level 0)
*(Panduan diagram gaya teks terstruktur untuk disalin/digambar ulang ke dokumen laporan)*

```text
[PENGELOLA TOKO] -- Data_Login, Data_Master, Data_Penjualan --------+
(Pemilik &       -- Data_Borongan, Data_Jadwal_Kirim ---------------+
 Keluarga)       -- Verifikasi_Proyek_&_Gaji, Data_Pengadaan -------+
                 <-- Info_Login, Notif_Stok_Minimum ----------------+
                 <-- Info_Jadwal_Kirim, Rekap_Gaji_Tukang ----------+
                 <-- Laporan_Operasional ---------------------------+
                                                                    |
                                                                    v
[PELANGGAN] -- Pesanan_Material, Pesanan_Borongan --> ( 0. Sistem Informasi Enterprise ) <-- Konfirmasi_Selesai -- [KARYAWAN TUKANG]
[PELANGGAN] <-- Nota_Penjualan, Nota_Borongan ------- (     Usaha Ridho Gipsum         ) -- Slip_Gaji -----------> [KARYAWAN TUKANG]
                                                                    ^
                                                                    |
                 [SUPPLIER] -- Nota_Pengadaan ----------------------+
                 [SUPPLIER] <-- Daftar_Pesanan_Bahan ---------------+
```

#### Kamus Data Sistem Informasi Enterprise ERPIDHO (Diperbarui)
*Keterangan Notasi: `=` terdiri dari; `+` dan; `[ A | B ]` pilihan salah satu; `{ ... }` perulangan; `( ... )` opsional.*

1. **Aliran Data Autentikasi & Master Data**:
   - `Data_Login` = `username + password`
   - `Info_Login` = `[ Notif_Login_Berhasil + id + kode_user + nama_lengkap + role | Notif_Login_Gagal ]`
   - `role` = `[ Pemilik | Keluarga ]`
   - `Data_Master` = `[ dtMaterial | dtPelanggan | dtTukang | dtSupplier | dtPengguna ]`
   - `dtMaterial` = `kode_barang + nama_barang + kategori + satuan + harga_beli + harga_jual + stok_sisa + batas_minimum`
   - `dtPelanggan` = `kode_pelanggan + nama_pelanggan + (no_telepon) + (alamat)`
   - `dtTukang` = `kode_tukang + nama_tukang + no_telepon + status_karyawan` *(kolom `tarif_pengerjaan` dihapus)*
   - `dtSupplier` = `kode_supplier + nama_supplier + (no_telepon) + (alamat) + (keterangan)`
   - `dtPengguna` = `kode_user + nama_lengkap + username + password + role`

2. **Aliran Data Penjualan Material Langsung (POS)**:
   - `Pesanan_Material` = `(pelanggan_id | nama_pelanggan_baru + (no_telepon_baru) + (alamat_baru)) + { material_id + jumlah } + perlu_pengiriman + (biaya_pengiriman) + metode_bayar + status_pembayaran + (tanggal_lunas)`
   - `Data_Penjualan` = `kode_transaksi + (pelanggan_id) + user_id + tanggal + perlu_pengiriman + biaya_pengiriman + { material_id + jumlah + harga_satuan + subtotal } + total_bayar + metode_bayar + status_pembayaran + (tanggal_lunas)`
   - `Nota_Penjualan` = `kode_transaksi + tanggal + (nama_pelanggan) + { nama_barang + satuan + jumlah + harga_satuan + subtotal } + (biaya_pengiriman) + total_bayar + metode_bayar + status_pembayaran + (tanggal_lunas)`

3. **Aliran Data Pesanan Borongan**:
   - `Pesanan_Borongan` = `(pelanggan_id | nama_pelanggan_baru + (no_telepon_baru) + (alamat_baru)) + tanggal_mulai + tanggal_target + lokasi_proyek + biaya_jasa + biaya_pengiriman + metode_bayar + status_pembayaran + (tanggal_lunas) + { material_id + jumlah_material }`
   - `Data_Borongan` = `kode_borongan + pelanggan_id + user_id + tanggal_mulai + tanggal_target + lokasi_proyek + { material_id + jumlah_material + harga_satuan + subtotal_material } + biaya_jasa + biaya_pengiriman + total_biaya + metode_bayar + status_pembayaran + (tanggal_lunas) + status_proyek`
   - `Nota_Borongan` = `kode_borongan + tanggal_mulai + tanggal_target + nama_pelanggan + lokasi_proyek + { nama_barang + satuan + jumlah_material + harga_satuan + subtotal_material } + biaya_jasa + (biaya_pengiriman) + total_biaya + metode_bayar + status_pembayaran + (tanggal_lunas) + status_proyek`

4. **Aliran Data Penjadwalan Pengiriman Barang**:
   - `Data_Jadwal_Kirim` = `kode_jadwal + [ transaksi_penjualan_id | pesanan_borongan_id ] + alamat_pengiriman + tanggal_kirim + jam_kirim + biaya_pengiriman + status_pengiriman + (catatan_bentrok)`
   - `Info_Jadwal_Kirim` = `kode_jadwal + [ kode_transaksi | kode_borongan ] + alamat_pengiriman + tanggal_kirim + jam_kirim + biaya_pengiriman + status_pengiriman + (catatan_bentrok)`

5. **Aliran Data Verifikasi Proyek & Penggajian Tukang**:
   - `Konfirmasi_Selesai` = `pesanan_borongan_id + lokasi_proyek`
   - `Verifikasi_Proyek_&_Gaji` = `pesanan_borongan_id + status_proyek + tanggal_diproses + { karyawan_tukang_id + total_gaji + status_bayar + (tanggal_dibayar) + (catatan) }`
   - `Slip_Gaji` = `kode_gaji + kode_tukang + nama_tukang + kode_borongan + lokasi_proyek + tanggal_diproses + (tanggal_dibayar) + total_gaji + status_bayar + (catatan)`
   - `Rekap_Gaji_Tukang` = `{ kode_gaji + nama_tukang + kode_borongan + tanggal_diproses + (tanggal_dibayar) + total_gaji + status_bayar + (catatan) } + total_belum_dibayar`

6. **Aliran Data Stok Minimum & Pengadaan Bahan**:
   - `Notif_Stok_Minimum` = `{ kode_barang + nama_barang + kategori + stok_sisa + batas_minimum }`
   - `Daftar_Pesanan_Bahan` = `kode_pengadaan + tanggal_pesan + nama_supplier + { kode_barang + nama_barang + satuan + jumlah_pesan + harga_beli_satuan + subtotal } + total_pengadaan`
   - `Nota_Pengadaan` = `kode_pengadaan + tanggal_terima + status_penerimaan`
   - `Data_Pengadaan` = `kode_pengadaan + user_id + supplier_id + tanggal_pesan + (tanggal_terima) + { material_id + jumlah_pesan + harga_beli_satuan + subtotal } + total_pengadaan + status_penerimaan`

7. **Aliran Data Pelaporan Operasional**:
   - `Laporan_Operasional` = `dari + sampai + Laporan_Penjualan_Lunas + Laporan_Stok_Material + Laporan_Pengadaan_Diterima + Laporan_Gaji_Dibayar`
   - `Laporan_Penjualan_Lunas` = `{ kode_transaksi + tanggal_lunas + total_bayar } + { kode_borongan + tanggal_lunas + total_biaya } + total_pemasukan_lunas + total_piutang_belum_lunas`
   - `Laporan_Stok_Material` = `{ kode_barang + nama_barang + kategori + stok_sisa + batas_minimum + status_stok }`
   - `Laporan_Pengadaan_Diterima` = `total_pengadaan_diterima + total_pengadaan_dipesan`
   - `Laporan_Gaji_Dibayar` = `total_gaji_dibayar + total_gaji_belum_dibayar`

---

### 3.2.2 DFD Level 1 (Diperbaiki & Disinkronkan)
*(Seluruh celah pada DFD PRD lama—hubungan `4.0 → D7`, `5.0 ↔ D2`, `5.0 ← D13`, `3.0 ↔ D9`, serta `6.0 ← D10, D13`—telah diperbaiki)*

```text
[PENGELOLA TOKO]
  |  |  |  |
  |  |  |  +--> Data_Login, Data_Master ---------> ( 1.0 Mengelola Autentikasi ) <---> [D1 Pengguna]
  |  |  |  <--- Info_Login ----------------------- (     & Data Master         ) <---> [D2 Material], [D3 Pelanggan]
  |  |  |                                          (                           ) <---> [D4 Karyawan_Tukang], [D13 Supplier]
  |  |  |
  |  |  +-----> Data_Penjualan, Data_Borongan ---> ( 2.0 Mengelola Penjualan   ) <---> [D2 Material], [D3 Pelanggan]
  |  |          [PELANGGAN] -- Pesanan_Material -> (     & Pesanan Borongan    ) ----> [D5 Transaksi_Penjualan], [D6 Detail_Penjualan]
  |  |          [PELANGGAN] -- Pesanan_Borongan -> (                           ) ----> [D7 Pesanan_Borongan], [D8 Detail_Borongan]
  |  |          [PELANGGAN] <- Nota_Penjualan ---- (                           )
  |  |          [PELANGGAN] <- Nota_Borongan ----- (                           )
  |  |
  |  +--------> Data_Jadwal_Kirim ---------------> ( 3.0 Mengelola Penjadwalan ) <---- [D3 Pelanggan]
  |  <--------- Info_Jadwal_Kirim ---------------- (     Pengiriman            ) <---- [D5 Transaksi_Penjualan], [D7 Pesanan_Borongan]
  |                                                (                           ) <---> [D9 Jadwal_Pengiriman]
  |
  +-----------> Verifikasi_Proyek_&_Gaji --------> ( 4.0 Mengelola Verifikasi  ) <---> [D7 Pesanan_Borongan]
  <------------ Rekap_Gaji_Tukang ---------------- (     Proyek & Penggajian   ) <---- [D4 Karyawan_Tukang]
                [KARYAWAN TUKANG] -- Konfirmasi -> (                           ) <---> [D10 Penggajian_Tukang]
                [KARYAWAN TUKANG] <- Slip_Gaji --- (                           )

[PENGELOLA TOKO] -- Data_Pengadaan --------------> ( 5.0 Mengelola Pengadaan   ) <---> [D2 Material]
[PENGELOLA TOKO] <- Notif_Stok_Minimum ----------- (     & Pemantauan Stok     ) <---- [D13 Supplier]
[SUPPLIER] -------> Nota_Pengadaan --------------> (                           ) <---> [D11 Pengadaan], [D12 Detail_Pengadaan]
[SUPPLIER] <------- Daftar_Pesanan_Bahan --------- (                           )

[PENGELOLA TOKO] <- Laporan_Operasional ---------- ( 6.0 Mengelola Pelaporan   ) <---- [D2 Material], [D5 Transaksi_Penjualan]
                                                   (     Operasional           ) <---- [D7 Pesanan_Borongan], [D10 Penggajian_Tukang]
                                                                                 <---- [D11 Pengadaan], [D13 Supplier]
```

---

### 3.2.3 DFD Level 2 untuk Proses 2 (Penjualan & Pesanan Borongan)

```text
[PENGELOLA TOKO] -- Data_Penjualan, Data_Borongan ----+
                                                      v
[PELANGGAN] ------- Pesanan_Material, Pesanan_Borongan --> ( 2.1 Validasi Pelanggan &   ) <---> [D3 Pelanggan]
                                                           (     Cek Ketersediaan Stok  ) <--- Info_Stok_&_Harga --- [D2 Material]
                                                              |                      |
                                      dtPesanan_Material_Valid|                      |dtPesanan_Borongan_Valid
                                                              v                      v
                                                ( 2.2 Pencatatan Transaksi )   ( 2.3 Pencatatan Pesanan )
                                                (     Penjualan Material   )   (     Paket Borongan     )
                                                  |        |        |            |        |        |
[D5 Transaksi_Penjualan] <------------------------+        |        |            |        |        +--> [D7 Pesanan_Borongan]
[D6 Detail_Penjualan]    <------------------------+        |        v            v        |        +--> [D8 Detail_Borongan]
                                                           |    ( 2.4 Pembaruan Stok )    |
                                           dtNota_Jual_Siap|    (     Material       )    |dtNota_Borongan_Siap
                                                           |             |                |
                                                           |             v                |
                                                           |       [D2 Material]          |
                                                           |                              |
                                                           +---> ( 2.5 Penerbitan ) <-----+
                                                                 (     Nota PDF   )
                                                                   |            |
                                                     Nota_Penjualan|            |Nota_Borongan
                                                                   v            v
                                                                    [PELANGGAN]
```

---

### 3.2.4 Proses Spesifikasi (`Tabel 3` — Diperbarui)

**Tabel 3. Proses Spesifikasi**

| No. | Nama Proses | Input | Output | Spesifikasi / Logika Proses |
| :---: | :--- | :--- | :--- | :--- |
| **1.0** | Mengelola Autentikasi & Data Master | `Data_Login`, `Data_Master` | `Info_Login`, Update `D1`, `D2`, `D3`, `D4`, `D13` | 1. **Autentikasi**: Mencocokkan `username` dan hash kata sandi pada `D1 Pengguna` (dilindungi *rate limiter* 10/menit). Mengembalikan sesi dengan peran (`Pemilik`/`Keluarga`).<br>2. **Data Master**: `Pemilik` dapat mengelola seluruh master (`Material`, `Pelanggan`, `Karyawan_Tukang`, `Supplier`, `Pengguna`). `Keluarga` hanya dapat melihat `Material` dan mengelola `Pelanggan`. Penghapusan diblokir bila data sudah berelasi ke transaksi/pengadaan/gaji. |
| **2.1** | Validasi Pelanggan & Cek Ketersediaan Stok | `Pesanan_Material`, `Pesanan_Borongan` | `dtPesanan_Material_Valid`, `dtPesanan_Borongan_Valid`, Update `D3` | 1. **Pelanggan**: Memilih `pelanggan_id` yang ada, membuat pelanggan baru di `D3` (wajib pada borongan, opsional pada POS), atau membiarkan kosong (*Pelanggan Umum* pada POS).<br>2. **Cek Stok**: Di dalam transaksi database (`lockForUpdate()`), menggabungkan kuantitas per `material_id` pada `D2 Material` dan memvalidasi `stok_sisa >= jumlah_diminta`. |
| **2.2** | Pencatatan Transaksi Penjualan Material | `dtPesanan_Material_Valid` | `dtItem_Terjual`, `dtNota_Jual_Siap`, Simpan `D5`, `D6` | 1. Membuat kode unik `TRX-XXXXXX`.<br>2. Menghitung `subtotal = jumlah * harga_jual` dari `D2`, ditambah `biaya_pengiriman` jika `perlu_pengiriman = true`.<br>3. Menyimpan `tanggal_lunas` (`<= hari ini`) jika `status_pembayaran = Lunas`.<br>4. Mengubah `Lunas → Belum Lunas` hanya boleh oleh `Pemilik`. |
| **2.3** | Pencatatan Pesanan Paket Borongan | `dtPesanan_Borongan_Valid` | `dtItem_Borongan`, `dtNota_Borongan_Siap`, Simpan `D7`, `D8` | 1. Membuat kode unik `BRN-XXXXXX` dengan `status_proyek = Menunggu`.<br>2. Memvalidasi `tanggal_mulai >= hari ini` dan `tanggal_target >= tanggal_mulai`.<br>3. Menghitung `total_biaya = SUM(subtotal_material) + biaya_jasa + biaya_pengiriman` serta mencatat `metode_bayar`, `status_pembayaran`, dan `tanggal_lunas`.<br>4. Perubahan status proyek maju searah; verifikasi `Selesai` hanya oleh `Pemilik` dan terkunci. |
| **2.4** | Pembaruan Stok Material | `dtItem_Terjual`, `dtItem_Borongan` | Update `D2 Material` | Mengurangi `stok_sisa` pada `D2 Material` secara atomik dalam transaksi yang sama dengan 2.2/2.3 (`stok_sisa = stok_sisa - jumlah`). |
| **2.5** | Penerbitan Nota | `dtNota_Jual_Siap`, `dtNota_Borongan_Siap` | `Nota_Penjualan`, `Nota_Borongan` (PDF) | Menyusun detail item, harga satuan *snapshot*, biaya jasa/kirim, status pelunasan, dan total ke dalam templat PDF siap unduh (*DomPDF*). |
| **3.0** | Mengelola Penjadwalan Pengiriman | `Data_Jadwal_Kirim` | `Info_Jadwal_Kirim`, Simpan/Update `D9` | 1. Memvalidasi referensi (`pesanan_borongan_id` atau `transaksi_penjualan_id` dengan `perlu_pengiriman = true`) belum terjadwal, menyalin `biaya_pengiriman` secara *read-only* tanpa mengubah total pesanan.<br>2. Memvalidasi `tanggal_kirim >= hari ini` (dan `jam_kirim >= waktu saat ini` bila hari ini).<br>3. Memeriksa jadwal aktif di `D9`: blokir keras jika tepat sama; tampilkan peringatan konfirmasi jika selisih ≤ 1 jam (`catatan_bentrok`).<br>4. Mengontrol transisi maju `Terjadwal → Dikirim → Selesai` (terkunci). |
| **4.0** | Mengelola Verifikasi Proyek & Penggajian | `Konfirmasi_Selesai`, `Verifikasi_Proyek_&_Gaji` | `Slip_Gaji` (PDF), `Rekap_Gaji_Tukang`, Update `D7`, Simpan/Update `D10` | 1. Memperbarui `status_proyek = Selesai` pada `D7`.<br>2. Memvalidasi `tanggal_diproses` & `tanggal_dibayar <= hari ini`, mencegah duplikasi tukang (`distinct` + cek `D10`), serta menyimpan `total_gaji` manual dan `catatan` opsional.<br>3. Transisi `Belum Dibayar → Dibayar` mengisi `tanggal_dibayar` dan mengunci record secara permanen. |
| **5.0** | Mengelola Pengadaan & Pemantauan Stok | `Data_Pengadaan`, `Nota_Pengadaan` | `Notif_Stok_Minimum`, `Daftar_Pesanan_Bahan` (PDF), Update `D2`, Simpan/Update `D11`, `D12` | 1. **Pantau Stok**: Menyorot item `D2` dengan `stok_sisa <= batas_minimum` beserta tombol pengadaan otomatis.<br>2. **Pemesanan**: Memvalidasi `tanggal_pesan <= hari ini` dan item `distinct`, menyimpan PO ke `D11` (`Dipesan`) dan `D12` berelasi ke `D13 Supplier`.<br>3. **Penerimaan**: Menggunakan `lockForUpdate()` saat konfirmasi terima (`Diterima`), mengisi `tanggal_terima`, menambah `stok_sisa`, dan memperbarui `harga_beli` di `D2`. |
| **6.0** | Mengelola Pelaporan Operasional | Data `D2`, `D5`, `D7`, `D10`, `D11`, `D13` | `Laporan_Operasional` (Web & PDF) | Merekap berdasarkan rentang `[dari, sampai]`:<br>1. **Pemasukan Lunas**: `SUM(total_bayar)` `D5` + `SUM(total_biaya)` `D7` dengan `status_pembayaran = Lunas` pada `tanggal_lunas`.<br>2. **Pengadaan Diterima**: `SUM(total_pengadaan)` `D11` dengan `status_penerimaan = Diterima` pada `tanggal_terima`.<br>3. **Pengeluaran Gaji Dibayar**: `SUM(total_gaji)` `D10` dengan `status_bayar = Dibayar` pada `tanggal_dibayar`.<br>4. **Stok & Piutang/Kewajiban**: Daftar stok `D2` + total `Belum Lunas` & `Belum Dibayar`. |

---

## 3.3 Deskripsi Data & Entitas Basis Data (Diperbarui)
Seluruh tabel diimplementasikan menggunakan *Surrogate Primary Key* (`id` auto-increment) agar kompatibel di **SQLite** maupun **Supabase PostgreSQL**, sementara kode unik bisnis dari PRD dipertahankan pada kolom `kode_*` (`unique`).

> **Legenda Penanda Perubahan (terhadap Tabel 4–15 PRD)**:
> * `Tetap` — Atribut sesuai dengan spesifikasi awal PRD.
> * **`[BARU]`** — Entitas/atribut baru pada implementasi aktual (termasuk `id` *surrogate PK* & `timestamps` `created_at`/`updated_at` standar Laravel).
> * **`[DIUBAH]`** — Atribut yang berubah nama, tipe representasi, konvensi PK/FK, atau batasan (*range*/*default*/*null*) dari PRD (misalnya kode bisnis `id_*` PRD yang menjadi kolom `kode_* unique`).
> * **`[DIHAPUS]`** — Atribut pada PRD/Kamus Data yang ditiadakan atau digantikan entitas terpisah pada implementasi aktual.

### Tabel 4 (Diperbarui): Deskripsi Entitas Pengguna (`users`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[BARU]`** (*Surrogate PK* Laravel) |
| `kode_user` | String | `USR-XXX` | 10 | Unik (`USR-001` dst.) | - | Tidak | **`[DIUBAH]`** (pengganti PK `id_user` PRD) |
| `nama_lengkap` | String | Huruf | 50 | `A..Z, a..z, spasi` | - | Tidak | `Tetap` |
| `username` | String | Alfanumerik | 30 | `a..z, 0..9, _` (unik) | - | Tidak | `Tetap` |
| `password` | Password | Hash String | 255 | Terenkripsi (Bcrypt) | - | Tidak | `Tetap` |
| `role` | String | Teks | 15 | `Pemilik, Keluarga` | `Keluarga` | Tidak | **`[DIUBAH]`** (String portabel + *default* eksplisit) |
| `remember_token` | String | Token | 100 | Karakter sesi acak | `NULL` | Ya | **`[BARU]`** (sesi autentikasi Laravel) |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

### Tabel 5 (Diperbarui): Deskripsi Entitas Material (`materials`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[BARU]`** (*Surrogate PK* Laravel) |
| `kode_barang` | String | `BRG-XXXX` | 10 | Unik (`BRG-0001` dst.) | - | Tidak | **`[DIUBAH]`** (pengganti PK `id_barang` PRD) |
| `nama_barang` | String | Alfanumerik | 100 | Semua karakter | - | Tidak | `Tetap` |
| `kategori` | String | Teks | 30 | `Gipsum, PVC, Rangka, Aksesoris` | - | Tidak | **`[DIUBAH]`** (String portabel) |
| `satuan` | String | Teks | 15 | `Lembar, Batang, Sak, Kotak` | - | Tidak | **`[DIUBAH]`** (String portabel) |
| `harga_beli` | Number | Rupiah (IDR) | BigInt | `>= 0` | `0` | Tidak | `Tetap` |
| `harga_jual` | Number | Rupiah (IDR) | BigInt | `>= 0` | `0` | Tidak | `Tetap` |
| `stok_sisa` | Number | Angka (Integer) | Int | `>= 0` | `0` | Tidak | `Tetap` |
| `batas_minimum` | Number | Angka (Integer) | Int | `>= 1` | `5` | Tidak | `Tetap` |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

### Tabel 6 (Diperbarui): Deskripsi Entitas Pelanggan (`pelanggans`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[BARU]`** (*Surrogate PK* Laravel) |
| `kode_pelanggan` | String | `PLG-XXXX` | 10 | Unik (`PLG-0001` dst.) | - | Tidak | **`[DIUBAH]`** (pengganti PK `id_pelanggan` PRD) |
| `nama_pelanggan` | String | Huruf | 60 | `A..Z, a..z, spasi` | - | Tidak | `Tetap` |
| `no_telepon` | String | Angka | 15 | `0..9, +` | `NULL` | Ya | `Tetap` |
| `alamat` | String | Alfanumerik | 150 | Semua karakter | `NULL` | Ya | `Tetap` |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

### Tabel 7 (Diperbarui): Deskripsi Entitas Karyawan_Tukang (`karyawan_tukangs`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[BARU]`** (*Surrogate PK* Laravel) |
| `kode_tukang` | String | `TKG-XXX` | 10 | Unik (`TKG-001` dst.) | - | Tidak | **`[DIUBAH]`** (pengganti PK `id_tukang` PRD) |
| `nama_tukang` | String | Huruf | 60 | `A..Z, a..z, spasi` | - | Tidak | `Tetap` |
| `no_telepon` | String | Angka | 15 | `0..9, +` | - | Tidak | `Tetap` |
| `status_karyawan` | String | Teks | 20 | `Aktif, Nonaktif` | `Aktif` | Tidak | **`[BARU]`** (dipulihkan dari Kamus Data PRD ke skema tabel) |
| ~~`tarif_pengerjaan`~~ | ~~Number~~ | ~~Rupiah (IDR)~~ | - | - | - | - | **`[DIHAPUS]`** (ada di Kamus Data PRD, namun upah diinput manual per slip gaji) |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

### Tabel Tambahan (D13): Deskripsi Entitas Supplier (`suppliers`) `[ENTITAS BARU]`

> Entitas baru hasil normalisasi atribut teks `nama_supplier` yang sebelumnya menempel pada tabel `Pengadaan` (Tabel 12 PRD).

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[BARU]`** |
| `kode_supplier` | String | `SUP-XXX` | 10 | Unik (`SUP-001` dst.) | - | Tidak | **`[BARU]`** |
| `nama_supplier` | String | Alfanumerik | 80 | Semua karakter | - | Tidak | **`[BARU]`** (dipindah dari `Pengadaan.nama_supplier`) |
| `no_telepon` | String | Angka | 15 | `0..9, +` | `NULL` | Ya | **`[BARU]`** |
| `alamat` | String | Alfanumerik | 150 | Semua karakter | `NULL` | Ya | **`[BARU]`** |
| `keterangan` | String | Alfanumerik | 150 | Semua karakter | `NULL` | Ya | **`[BARU]`** |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** |

---

### Tabel 8 (Diperbarui): Deskripsi Entitas Transaksi_Penjualan (`transaksi_penjualans`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[BARU]`** (*Surrogate PK* Laravel) |
| `kode_transaksi` | String | `TRX-XXXXXX` | 15 | Unik (`TRX-000001` dst.) | - | Tidak | **`[DIUBAH]`** (pengganti PK `id_transaksi` PRD) |
| `pelanggan_id` (FK) | Integer | Referensi `pelanggans.id` | BigInt | ID Pelanggan terdaftar | `NULL` | Ya | **`[DIUBAH]`** (FK ke `pelanggans.id`; opsional untuk kasir cepat) |
| `user_id` (FK) | Integer | Referensi `users.id` | BigInt | ID Pengguna aktif | - | Tidak | **`[DIUBAH]`** (FK ke `users.id`) |
| `tanggal` | DateTime | `YYYY-MM-DD HH:MM` | - | Waktu transaksi (terindeks) | Auto | Tidak | **`[DIUBAH]`** (ditambah indeks & batas tanggal mundur di UI) |
| `perlu_pengiriman` | Boolean | `true / false` | 1 | `0 (Ambil di Toko), 1 (Kirim)` | `false` | Tidak | **`[BARU]`** (opsi kirim langsung di form POS) |
| `biaya_pengiriman` | Number | Rupiah (IDR) | BigInt | `>= 0` | `0` | Tidak | **`[BARU]`** (ongkos kirim masuk `total_bayar`) |
| `total_bayar` | Number | Rupiah (IDR) | BigInt | `>= 0` (Material + Ongkir) | `0` | Tidak | **`[DIUBAH]`** (mencakup `biaya_pengiriman`) |
| `metode_bayar` | String | Teks | 15 | `Tunai, Transfer, QRIS` | `Tunai` | Tidak | **`[DIUBAH]`** (String portabel + *default*) |
| `status_pembayaran` | String | Teks | 15 | `Lunas, Belum Lunas` | `Lunas` | Tidak | **`[DIUBAH]`** (koreksi `Lunas` → `Belum Lunas` khusus `Pemilik`) |
| `tanggal_lunas` | Date | `YYYY-MM-DD` | - | Tanggal kas diterima (terindeks) | `NULL` | Ya | **`[BARU]`** (dasar pelaporan *cash basis*) |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

### Tabel 9 (Diperbarui): Deskripsi Entitas Pesanan_Borongan (`pesanan_borongans`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[BARU]`** (*Surrogate PK* Laravel) |
| `kode_borongan` | String | `BRN-XXXXXX` | 15 | Unik (`BRN-000001` dst.) | - | Tidak | **`[DIUBAH]`** (pengganti PK `id_borongan` PRD) |
| `pelanggan_id` (FK) | Integer | Referensi `pelanggans.id` | BigInt | ID Pelanggan terdaftar | - | Tidak | **`[DIUBAH]`** (FK ke `pelanggans.id`) |
| `user_id` (FK) | Integer | Referensi `users.id` | BigInt | ID Pengguna aktif | - | Tidak | **`[DIUBAH]`** (FK ke `users.id`) |
| `tanggal_mulai` | Date | `YYYY-MM-DD` | - | `>= Hari ini` (terindeks) | - | Tidak | **`[DIUBAH]`** (ditambah indeks & validasi tidak mundur) |
| `tanggal_target` | Date | `YYYY-MM-DD` | - | `>= tanggal_mulai` | - | Tidak | `Tetap` |
| `lokasi_proyek` | String | Alfanumerik | 150 | Semua karakter | - | Tidak | `Tetap` |
| `biaya_jasa` | Number | Rupiah (IDR) | BigInt | `>= 0` | `0` | Tidak | `Tetap` |
| `biaya_pengiriman` | Number | Rupiah (IDR) | BigInt | `>= 0` | `0` | Tidak | **`[BARU]`** (ongkos kirim paket borongan) |
| `total_biaya` | Number | Rupiah (IDR) | BigInt | `>= 0` (Material + Jasa + Ongkir) | `0` | Tidak | **`[DIUBAH]`** (mencakup `biaya_pengiriman`) |
| `metode_bayar` | String | Teks | 15 | `Tunai, Transfer, QRIS` | `Tunai` | Tidak | **`[BARU]`** (pembayaran terintegrasi di borongan) |
| `status_pembayaran` | String | Teks | 15 | `Lunas, Belum Lunas` | `Belum Lunas` | Tidak | **`[BARU]`** (pelacakan pelunasan proyek borongan) |
| `tanggal_lunas` | Date | `YYYY-MM-DD` | - | Tanggal kas diterima (terindeks) | `NULL` | Ya | **`[BARU]`** (dasar pelaporan *cash basis*) |
| `status_proyek` | String | Teks | 20 | `Menunggu, Berjalan, Selesai` | `Menunggu` | Tidak | **`[DIUBAH]`** (transisi maju satu arah; `Selesai` terkunci) |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

### Tabel 10 (Diperbarui): Deskripsi Entitas Jadwal_Pengiriman (`jadwal_pengirimen`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[BARU]`** (*Surrogate PK* Laravel) |
| `kode_jadwal` | String | `JDW-XXXXXX` | 15 | Unik (`JDW-000001` dst.) | - | Tidak | **`[DIUBAH]`** (pengganti PK `id_jadwal` PRD) |
| `transaksi_penjualan_id` (FK) | Integer | Referensi `transaksi_penjualans.id` | BigInt | ID Transaksi (opsional) | `NULL` | Ya | **`[DIUBAH]`** (FK ke `transaksi_penjualans.id`) |
| `pesanan_borongan_id` (FK) | Integer | Referensi `pesanan_borongans.id` | BigInt | ID Borongan (opsional) | `NULL` | Ya | **`[DIUBAH]`** (FK ke `pesanan_borongans.id`) |
| `alamat_pengiriman` | String | Alfanumerik | 150 | Semua karakter | - | Tidak | `Tetap` |
| `tanggal_kirim` | Date | `YYYY-MM-DD` | - | `>= Hari ini` (indeks gabungan) | - | Tidak | **`[DIUBAH]`** (validasi `after_or_equal:today`) |
| `jam_kirim` | String | `HH:MM` | 5 | `00:00 .. 23:59` (indeks gabungan) | - | Tidak | **`[DIUBAH]`** (dari `Time` ke `String(5)` portabel SQLite/PostgreSQL) |
| `biaya_pengiriman` | Number | Rupiah (IDR) | BigInt | `>= 0` | `0` | Tidak | **`[DIUBAH]`** (*read-only* salinan dari pesanan induk) |
| `status_pengiriman` | String | Teks | 20 | `Terjadwal, Dikirim, Selesai` | `Terjadwal` | Tidak | **`[DIUBAH]`** (transisi maju satu arah; `Selesai` terkunci) |
| `catatan_bentrok` | String | Alfanumerik | 255 | Pesan konfirmasi bentrok `<= 1 jam` | `NULL` | Ya | **`[BARU]`** (jejak audit bentrok jadwal lunak) |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

### Tabel 11 (Diperbarui): Deskripsi Entitas Penggajian_Tukang (`penggajian_tukangs`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[BARU]`** (*Surrogate PK* Laravel) |
| `kode_gaji` | String | `GAJ-XXXXXX` | 15 | Unik (`GAJ-000001` dst.) | - | Tidak | **`[DIUBAH]`** (pengganti PK `id_gaji` PRD) |
| `karyawan_tukang_id` (FK) | Integer | Referensi `karyawan_tukangs.id` | BigInt | ID Tukang aktif | - | Tidak | **`[DIUBAH]`** (FK ke `karyawan_tukangs.id`) |
| `pesanan_borongan_id` (FK) | Integer | Referensi `pesanan_borongans.id` | BigInt | ID Proyek Borongan | - | Tidak | **`[DIUBAH]`** (FK ke `pesanan_borongans.id`) |
| `tanggal_diproses` | Date | `YYYY-MM-DD` | - | `<= Hari ini` (terindeks) | - | Tidak | **`[DIUBAH]`** (diizinkan mundur untuk rapel; dilarang masa depan) |
| `tanggal_dibayar` | Date | `YYYY-MM-DD` | - | `<= Hari ini` (terindeks) | `NULL` | Ya | **`[BARU]`** (dasar arus kas keluar gaji pada laporan) |
| `total_gaji` | Number | Rupiah (IDR) | BigInt | `>= 0` (input manual) | `0` | Tidak | `Tetap` |
| `status_bayar` | String | Teks | 15 | `Belum Dibayar, Dibayar` | `Belum Dibayar` | Tidak | **`[DIUBAH]`** (`Dibayar` terkunci permanen satu arah) |
| `catatan` | Text | Alfanumerik | - | Catatan upah / lembur | `NULL` | Ya | **`[BARU]`** (keterangan tambahan slip gaji) |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

### Tabel 12 (Diperbarui): Deskripsi Entitas Pengadaan (`pengadaans`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[BARU]`** (*Surrogate PK* Laravel) |
| `kode_pengadaan` | String | `PGD-XXXXXX` | 15 | Unik (`PGD-000001` dst.) | - | Tidak | **`[DIUBAH]`** (pengganti PK `id_pengadaan` PRD) |
| `user_id` (FK) | Integer | Referensi `users.id` | BigInt | ID Pengguna aktif | - | Tidak | **`[DIUBAH]`** (FK ke `users.id`) |
| `supplier_id` (FK) | Integer | Referensi `suppliers.id` | BigInt | ID Supplier terdaftar | - | Tidak | **`[BARU]`** (menggantikan kolom teks `nama_supplier`) |
| ~~`nama_supplier`~~ | ~~String~~ | ~~Alfanumerik~~ | ~~80~~ | - | - | - | **`[DIHAPUS]`** (dinormalisasi ke tabel `suppliers`) |
| `tanggal_pesan` | Date | `YYYY-MM-DD` | - | Tanggal pemesanan (terindeks) | - | Tidak | `Tetap` |
| `tanggal_terima` | Date | `YYYY-MM-DD` | - | Tanggal penerimaan barang | `NULL` | Ya | **`[BARU]`** (dasar arus kas keluar pengadaan pada laporan) |
| `total_pengadaan` | Number | Rupiah (IDR) | BigInt | `>= 0` | `0` | Tidak | `Tetap` |
| `status_penerimaan` | String | Teks | 15 | `Dipesan, Diterima` | `Dipesan` | Tidak | **`[DIUBAH]`** (`Diterima` menambah stok & terkunci satu arah) |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

### Tabel 13 (Diperbarui): Deskripsi Relasi Detail_Penjualan (`detail_penjualans`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[DIUBAH]`** (pengganti `id_detail_jual` PRD) |
| `transaksi_penjualan_id` (FK) | Integer | Referensi `transaksi_penjualans.id` | BigInt | ID Transaksi valid | - | Tidak | **`[DIUBAH]`** (FK ke `transaksi_penjualans.id`, *cascade delete*) |
| `material_id` (FK) | Integer | Referensi `materials.id` | BigInt | ID Material terdaftar | - | Tidak | **`[DIUBAH]`** (FK ke `materials.id`) |
| `jumlah` | Number | Angka (Integer) | Int | `>= 1` | - | Tidak | `Tetap` |
| `harga_satuan` | Number | Rupiah (IDR) | BigInt | `>= 0` | - | Tidak | `Tetap` |
| `subtotal` | Number | Rupiah (IDR) | BigInt | `>= 0` | - | Tidak | `Tetap` |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

### Tabel 14 (Diperbarui): Deskripsi Relasi Detail_Borongan (`detail_borongans`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[DIUBAH]`** (pengganti `id_detail_borongan` PRD) |
| `pesanan_borongan_id` (FK) | Integer | Referensi `pesanan_borongans.id` | BigInt | ID Borongan valid | - | Tidak | **`[DIUBAH]`** (FK ke `pesanan_borongans.id`, *cascade delete*) |
| `material_id` (FK) | Integer | Referensi `materials.id` | BigInt | ID Material terdaftar | - | Tidak | **`[DIUBAH]`** (FK ke `materials.id`) |
| `jumlah_material` | Number | Angka (Integer) | Int | `>= 1` | - | Tidak | `Tetap` |
| `harga_satuan` | Number | Rupiah (IDR) | BigInt | `>= 0` (*snapshot* harga jual) | - | Tidak | **`[BARU]`** (mencegah perubahan harga master mengubah nota lama) |
| `subtotal_material` | Number | Rupiah (IDR) | BigInt | `>= 0` | - | Tidak | `Tetap` |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

### Tabel 15 (Diperbarui): Deskripsi Relasi Detail_Pengadaan (`detail_pengadaans`) `[DIUBAH]`

| Atribut | Representasi | Unit/Format | Presisi | Range | Default | Null | Status Perubahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` (PK) | Integer | Auto Increment | BigInt | `>= 1` | Auto | Tidak | **`[DIUBAH]`** (pengganti `id_detail_pengadaan` PRD) |
| `pengadaan_id` (FK) | Integer | Referensi `pengadaans.id` | BigInt | ID Pengadaan valid | - | Tidak | **`[DIUBAH]`** (FK ke `pengadaans.id`, *cascade delete*) |
| `material_id` (FK) | Integer | Referensi `materials.id` | BigInt | ID Material terdaftar | - | Tidak | **`[DIUBAH]`** (FK ke `materials.id`) |
| `jumlah_pesan` | Number | Angka (Integer) | Int | `>= 1` | - | Tidak | `Tetap` |
| `harga_beli_satuan` | Number | Rupiah (IDR) | BigInt | `>= 0` | - | Tidak | `Tetap` |
| `subtotal` | Number | Rupiah (IDR) | BigInt | `>= 0` | - | Tidak | `Tetap` |
| `created_at`, `updated_at` | DateTime | `YYYY-MM-DD HH:MM:SS` | - | Timestamp sistem | Auto | Ya | **`[BARU]`** (`timestamps` Laravel) |

---

## 3.4 – 3.6 Kebutuhan Non-Fungsional, Kualitas Perangkat Lunak, & Batasan Perancangan (Ringkasan Pembaruan)
* **Keamanan & Kendali (Bagian 3.4 & 3.5.3)**: Selain *password hashing*, pembatasan peran `Pemilik`/`Keluarga`, dan SSL/HTTPS, sistem menerapkan *rate limiting* `login` (`10/menit`), proteksi CSRF pada seluruh form, kunci status satu arah (`Selesai`, `Diterima`, `Dibayar`), hak koreksi `Lunas` eksklusif `Pemilik`, serta konfirmasi modal interaktif pada seluruh aksi sensitif.
* **Kemerawatan & Portabilitas (Bagian 3.5.4 – 3.5.5)**: Dikembangkan secara modular berbasis Laravel MVC + `StokService` dengan skema migrasi portabel (berjalan lokal di **SQLite** dan siap dipindahkan ke **Supabase PostgreSQL** hanya melalui konfigurasi `.env`), serta terverifikasi oleh **31 *automated feature tests* PHPUnit**.
* **Batasan Perancangan (Bagian 3.6)**: Menggunakan **PHP 8.5 (Laravel 13)**, **HTML5**, **Tailwind CSS v4**, **Alpine.js**, **Vite**, **`barryvdh/laravel-dompdf`**, dan basis data relasional portabel (**SQLite / Supabase PostgreSQL**).
