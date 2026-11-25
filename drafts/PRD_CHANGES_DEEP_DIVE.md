# Analisis Mendalam Perubahan Implementasi Web ERPIDHO terhadap PRD
*(Perbandingan aktual antara spesifikasi pada [PRD Erphido.pdf](PRD%20Erphido.pdf) dengan sistem web Laravel yang telah dibangun pada proyek ini, khususnya Bab II dan Bab III)*

---

## Ringkasan Eksekutif
Secara keseluruhan, seluruh 6 kebutuhan fungsional utama (`FR-01` s.d. `FR-06`) pada [PRD Erphido.pdf](PRD%20Erphido.pdf) telah terpenuhi. Namun, selama proses perancangan ulang (*refinement*), pengisian celah skema (*gap resolution*), serta penguatan kendali internal akuntansi dan keamanan, terjadi beberapa **penyesuaian signifikan** pada:
1. **Model Pembayaran & Akuntansi Berbasis Kas (*Cash-Basis*)**: Penjualan, Borongan, Pengadaan, dan Penggajian kini melacak tanggal realisasi kas/barang (`tanggal_lunas`, `tanggal_terima`, `tanggal_dibayar`).
2. **Pemisahan Biaya Pengiriman vs. Penjadwalan Logistik**: Biaya kirim ditetapkan saat pembuatan pesanan (bukan diubah saat menjadwalkan antar).
3. **Penyempurnaan Skema Data (Bab III)**: Penambahan entitas `suppliers`, penambahan kolom yang sempat hilang/inkonsisten pada PRD, pemakaian *surrogate primary key* (`id`), dan penghapusan `tarif_pengerjaan` menuju input upah manual + `catatan`.
4. **Kunci Status Satu Arah (*State Locks*) & Kendali Validasi Tanggal**: Mencegah manipulasi mundur pada status final serta memvalidasi tanggal jadwal vs. tanggal historis.

---

## 1. Perubahan pada Bab I & Bab III Bagian 3.6 (Teknologi & Infrastruktur)

| Aspek | Tertulis di PRD | Implementasi Web Saat Ini | Alasan Perubahan |
| :--- | :--- | :--- | :--- |
| **Basis Data** | MySQL (Bab III 3.6 & Abstrak) | **SQLite** (lokal dev) yang dirancang *database-agnostic* agar siap migrasi ke **Supabase (PostgreSQL)** | Kemudahan pengembangan lokal tanpa instalasi MySQL aktif serta fleksibilitas *cloud* PostgreSQL; migrasi menghindari tipe `ENUM` khusus MySQL. |
| **Frontend JS** | JavaScript standar | **Alpine.js** + Tailwind CSS v4 + Vite 8 | Dibutuhkan reaktivitas ringan untuk keranjang multi-baris dinamis (POS, Borongan, Pengadaan, Penggajian) dan modal konfirmasi global. |
| **Cetak Nota/Slip** | Format nota cetak/digital (tidak dirinci paketnya) | **`barryvdh/laravel-dompdf` (PDF)** untuk 5 jenis dokumen | Pengguna membutuhkan berkas PDF siap unduh dan cetak (*Nota Penjualan*, *Nota Borongan*, *Slip Gaji*, *Daftar Pesanan Bahan*, *Laporan Operasional*). |
| **Penamaan Proyek** | Cover PDF tertulis `ERPIDHO` dan di bagian lain `ERPIDHO` | Konsisten menggunakan **ERPIDHO** (`APP` & seluruh tampilan) | Menyeragamkan identitas merek *Usaha Ridho Gipsum*. |

---

## 2. Perubahan pada Bab II (Analisis Kebutuhan Sistem)

### 2.1 Teknik Pengumpulan Kebutuhan (Bagian 2.1)
* **Temuan pada PRD**: Paragraf pembuka menulis *"tiga kombinasi teknik utama"*, tetapi hanya mencantumkan 2 butir bernomor (*Wawancara Terstruktur* dan *Analisis Dokumen*). *(Disarankan diselaraskan saat merevisi naskah PRD/Bab II).*

### 2.2 Area Fungsional & Proses Bisnis (Bagian 2.2)
1. **Area Pengadaan (*Procurement*)**:
   * **PRD**: Nama *supplier* dicatat sebagai teks bebas pada tabel pengadaan.
   * **Web Saat Ini**: Dipisahkan menjadi **entitas Master Supplier (`suppliers`)** beserta fitur CRUD khusus *Pemilik*, sehingga data mitra pemasok terstandar dan tidak terjadi salah ketik nama berulang.
2. **Area Penjualan (`Sales & POS`) & Penjadwalan (`Scheduling & Delivery`)**:
   * **PRD (Proses 3.0)**: `biaya_pengiriman` diinput saat membuat `Jadwal_Pengiriman`, lalu ditambahkan belakangan ke `total_bayar` / `total_biaya` pesanan induk.
   * **Web Saat Ini**: Jika biaya pengiriman ditambahkan setelah transaksi POS berstatus `Lunas`, total tagihan akan naik sementara status tetap `Lunas`. Oleh karena itu, alur diubah:
     * Pada **POS (`TransaksiPenjualan`)**, ditambahkan opsi *"Perlu Pengiriman?"* (`perlu_pengiriman`) + input `biaya_pengiriman` langsung saat *checkout*.
     * Pada **Borongan (`PesananBorongan`)**, `biaya_pengiriman` diinput langsung bersama `biaya_jasa`.
     * **Jadwal Pengiriman** murni mengelola logistik (`tanggal_kirim`, `jam_kirim`, `alamat_pengiriman`, status antar) tanpa mengubah total tagihan pesanan.
3. **Area Penggajian Tukang (`Payroll & Workforce Management`)**:
   * **PRD**: Mengasumsikan ada kolom `tarif_pengerjaan` pada `Karyawan_Tukang` dan menghitung gaji otomatis dari tarif tetap tersebut.
   * **Web Saat Ini**: Tukang ditugaskan **langsung saat verifikasi/penggajian proyek** dengan nominal **`total_gaji` diisi manual** oleh *Pemilik*, disertai kolom opsional **`catatan`** (misalnya lembur, bonus, atau potongan kasbon) dan **`tanggal_dibayar`** untuk pencatatan kas keluar.

### 2.3 Identifikasi Event (Bagian 2.3.2)
* **State Event — Bentrok Jadwal Pengiriman**:
  * **PRD**: Hanya menyebut *"Terjadi bentrokan jadwal pengantaran barang dan tukang pada waktu yang sama"*.
  * **Web Saat Ini**: Diperjelas menjadi aturan dua tingkat:
    1. **Bentrok tepat waktu** (`tanggal_kirim` dan `jam_kirim` persis sama dengan jadwal aktif lain) → **Blokir mutlak (*hard block*)** disertai alasan nomor jadwal dan alamat bentrok.
    2. **Bentrok berdekatan (selisih ≤ 1 jam)** → **Peringatan (*warning*)** yang mengizinkan penyimpanan hanya setelah pengguna mencentang konfirmasi persetujuan (tercatat di `catatan_bentrok`).
* **Validasi Waktu/Kalender Baru**:
  * Jadwal mendatang (`tanggal_kirim`, `jam_kirim` hari ini, dan `tanggal_mulai` borongan) diblokir jika berada di masa lalu.
  * Tanggal historis (`tanggal_diproses`, `tanggal_dibayar`, `tanggal_lunas`, `tanggal_pesan`) mengizinkan tanggal lampau (jika pemilik baru sempat mencatat), tetapi diblokir jika melebihi hari ini (`<= today`).

---

## 3. Perubahan Mendalam pada Bab III (Perancangan Data)

### 3.1 Perubahan pada Kebutuhan Fungsional (`Tabel 2`, FR-01 s.d. FR-06)

| Kode | Perubahan dari PRD ke Sistem Web Aktual |
| :--- | :--- |
| **FR-01** | Ditambahkan pengelolaan data master **Supplier** (`SUP-XXX`). Pembatasan hak akses dipertegas: peran **`Keluarga` dapat melihat daftar Material** (untuk cek stok/harga saat melayani) dan mengelola **Pelanggan**, namun **diblokir (`403`)** dari ubah/hapus Material, Supplier, Karyawan Tukang, dan Pengguna. |
| **FR-02** | • Ditambahkan opsi `perlu_pengiriman` + `biaya_pengiriman` saat input POS.<br>• Ditambahkan `metode_bayar`, `status_pembayaran`, `tanggal_lunas`, dan `biaya_pengiriman` pada Pesanan Borongan.<br>• Status `status_proyek` bersifat **maju satu arah** (`Menunggu → Berjalan → Selesai`) dan terkunci saat `Selesai` (`Keluarga` diblokir memverifikasi `Selesai` maupun mengembalikan status).<br>• Pembatalan pelunasan (`Lunas → Belum Lunas`) pada POS & Borongan dibatasi khusus **`Pemilik`** (`403` bagi `Keluarga`). |
| **FR-03** | • `JadwalPengiriman` hanya menerima pesanan Borongan atau POS yang memilih `perlu_pengiriman = true` (dan belum terjadwal).<br>• Dihapusnya input/mutasi `biaya_pengiriman` pada form jadwal (hanya menampilkan salinan nilai dari pesanan induk).<br>• Status pengiriman bersifat maju satu arah (`Terjadwal → Dikirim → Selesai`) dan terkunci pada `Selesai`. |
| **FR-04** | • Dihapusnya ketergantungan pada `tarif_pengerjaan`; penugasan tukang dan `total_gaji` dilakukan manual oleh `Pemilik` per proyek.<br>• Ditambahkan kolom `catatan` dan `tanggal_dibayar`.<br>• Dicegah duplikasi tukang pada pengajuan yang sama maupun proyek borongan yang sama.<br>• Status `Dibayar` **dikunci permanen satu arah** (tidak dapat dikembalikan ke `Belum Dibayar`). |
| **FR-05** | • Form pengadaan menggunakan relasi `supplier_id` (bukan input teks bebas).<br>• Ditambahkan tombol pintas **`+ Buat Pengadaan Stok Kritis`** yang otomatis mengisi baris material dengan `stok_sisa <= batas_minimum`.<br>• Dicegah duplikasi `material_id` dalam satu pesanan pengadaan (`distinct`), serta dikuncinya status `Diterima` agar stok tidak bertambah ganda. |
| **FR-06** | • Laporan berubah dari akrual (*berdasarkan tanggal buat*) menjadi **berbasis kas & realisasi**:<br>  - **Pemasukan Lunas**: `TransaksiPenjualan` + `PesananBorongan` berstatus `Lunas` berdasarkan **`tanggal_lunas`** dalam rentang `[dari, sampai]`.<br>  - **Pengadaan Diterima**: `Pengadaan` berstatus `Diterima` berdasarkan **`tanggal_terima`** dalam rentang `[dari, sampai]`.<br>  - **Pengeluaran Gaji Dibayar**: `PenggajianTukang` berstatus `Dibayar` berdasarkan **`tanggal_dibayar`** dalam rentang `[dari, sampai]`.<br>• Ditambahkan informasi sekunder **Piutang Belum Lunas** dan **Gaji Belum Dibayar**, serta ekspor **Laporan Operasional PDF**. |

---

### 3.2 Perubahan pada Kamus Data & Spesifikasi Proses (`Bagian 3.2.1` & `Tabel 3`)

1. **Perbaikan Inkonsistensi Prefix Kode Borongan**:
   * Di Kamus Data (hal. 14) dan Proses 2.3 (hal. 17), PRD sempat menulis format kode borongan **`BRG-XXXXXX`**, yang bentrok dengan kode `Material` (`BRG-XXXX`). Sementara Tabel 9 (hal. 22) menulis **`BRN-XXXXXX`**.
   * **Implementasi Web**: Dibakukan menjadi **`BRG-XXXX`** untuk `Material` dan **`BRN-XXXXXX`** untuk `PesananBorongan` (plus `SUP-XXX` untuk `Supplier`).
2. **Perubahan Aliran Data Utama pada Kamus Data**:
   * `dtTukang`: Menghapus `tarif_pengerjaan` dari entitas master tukang.
   * `Data_Penjualan`: Menambah `perlu_pengiriman` dan `tanggal_lunas`.
   * `Data_Borongan`: Menambah `biaya_pengiriman`, `metode_bayar`, `status_pembayaran`, `tanggal_lunas`.
   * `Data_Jadwal_Kirim`: Menambah `catatan_bentrok`; tidak lagi mengirim sinyal pembaruan nominal biaya ke D5 (`Transaksi_Penjualan`) dan D7 (`Pesanan_Borongan`).
   * `Verifikasi_Proyek_&_Gaji`: Menambah `tanggal_dibayar` dan `catatan`; tidak lagi membaca `tarif_pengerjaan` dari D4.
   * `Data_Pengadaan`: Mengganti `nama_supplier` menjadi `supplier_id` (relasi ke tabel baru `suppliers`) dan menyimpan `tanggal_terima`.
3. **Pembaruan Logika Proses pada `Tabel 3`**:
   * **Proses 2.1–2.4**: Pengurangan stok dilakukan melalui `StokService::deductMaterials()` dengan `lockForUpdate()` dan penggabungan kuantitas jika material yang sama dipilih pada beberapa baris.
   * **Proses 3.0**: Langkah ke-3 pada PRD (*memperbarui `total_bayar` D5 / `total_biaya` D7 jika `biaya_pengiriman > 0`*) **dihapus** karena biaya kirim sudah digabungkan pada Proses 2.2/2.3.
   * **Proses 5.0**: Saat `Nota_Pengadaan` dikonfirmasi diterima (`receive`), selain menambah `stok_sisa`, sistem juga memperbarui `harga_beli` pada `materials` jika `harga_beli_satuan > 0`.

---

### 3.3 Perubahan pada Deskripsi Data & ER-Diagram (`Bagian 3.3`, `Gambar 4`, & `Tabel 4`–`Tabel 15`)

#### A. Perubahan Struktural Umum
* **Surrogate Primary Key (`id`)**: Pada PRD (Tabel 4–15), kolom kode bisnis (`id_user`, `id_barang`, `id_transaksi`, dll.) dijadikan *Primary Key* bertipe `String` sekaligus *Foreign Key*. Pada implementasi Laravel, setiap tabel menggunakan *Primary Key* `id` (`auto-increment`) untuk integrasi autentikasi dan indeks *join* yang efisien, sementara kode bisnis PRD tetap dipertahankan sebagai kolom `unique` (`kode_user`, `kode_barang`, `kode_pelanggan`, `kode_tukang`, `kode_supplier`, `kode_transaksi`, `kode_borongan`, `kode_jadwal`, `kode_gaji`, `kode_pengadaan`) beserta `created_at` & `updated_at` (`timestamps`).
* **Tabel Dihapus/Disiapkan**: `password_reset_tokens` tidak dibuat pada migrasi awal karena autentikasi memakai `username` (tanpa email), sedangkan tabel `sessions`, `cache`, dan `jobs` disertakan untuk operasional Laravel.

#### B. Perbandingan Kolom per Entitas (PRD vs. Migrasi Aktual)

1. **Entitas Pengguna (`users` — Tabel 4 PRD)**:
   * *PRD*: `id_user`, `nama_lengkap`, `username`, `password`, `role`.
   * *Web*: `id`, `kode_user`, `nama_lengkap`, `username`, `password`, `role`, `remember_token`, `timestamps`.
2. **Entitas Material (`materials` — Tabel 5 PRD)**:
   * *PRD*: `id_barang`, `nama_barang`, `kategori`, `satuan`, `harga_beli`, `harga_jual`, `stok_sisa`, `batas_minimum`.
   * *Web*: Sama persis (dengan `id`, `kode_barang`, dan `timestamps`; harga disimpan sebagai `bigInteger` Rupiah).
3. **Entitas Pelanggan (`pelanggans` — Tabel 6 PRD)**:
   * *PRD*: `id_pelanggan`, `nama_pelanggan`, `no_telepon`, `alamat`.
   * *Web*: Sama persis (dengan `id`, `kode_pelanggan`, dan `timestamps`).
4. **Entitas Karyawan Tukang (`karyawan_tukangs` — Tabel 7 PRD)**:
   * *PRD*: Di Kamus Data (hal. 14) memiliki `status_karyawan` dan `tarif_pengerjaan`, tetapi di Tabel 7 & ER-Diagram (hal. 21–22) keduanya hilang sehingga hanya tersisa `id_tukang`, `nama_tukang`, `no_telepon`.
   * *Web*: Menyertakan **`status_karyawan`** (`Aktif`/`Nonaktif`) dan **sengaja tidak menyertakan `tarif_pengerjaan`** karena upah diinput manual per proyek di modul penggajian.
5. **Entitas Supplier (`suppliers` — Entitas Baru)**:
   * *PRD*: Tidak ada di ER-Diagram / Tabel Entitas.
   * *Web*: Ditambahkan tabel `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `no_telepon`, `alamat`, `keterangan`, `timestamps`) yang berelasi *one-to-many* ke `pengadaans`.
6. **Entitas Transaksi Penjualan (`transaksi_penjualans` — Tabel 8 PRD)**:
   * *PRD*: `id_transaksi`, `id_pelanggan`, `id_user`, `tanggal`, `total_bayar`, `metode_bayar`, `status_pembayaran`.
   * *Web*: Menambahkan **`perlu_pengiriman`** (`boolean`), **`biaya_pengiriman`** (`bigInteger`), dan **`tanggal_lunas`** (`date`, nullable, indexed).
7. **Entitas Pesanan Borongan (`pesanan_borongans` — Tabel 9 PRD)**:
   * *PRD*: `id_borongan`, `id_pelanggan`, `id_user`, `tanggal_mulai`, `tanggal_target`, `lokasi_proyek`, `biaya_jasa`, `total_biaya`, `status_proyek`.
   * *Web*: Menambahkan **`biaya_pengiriman`**, **`metode_bayar`**, **`status_pembayaran`**, dan **`tanggal_lunas`**.
8. **Relasi Detail Penjualan (`detail_penjualans` — Tabel 13 PRD)**:
   * *PRD*: `id_detail_jual`, `id_transaksi`, `id_barang`, `jumlah`, `harga_satuan`, `subtotal`.
   * *Web*: Sama (`id`, `transaksi_penjualan_id`, `material_id`, `jumlah`, `harga_satuan`, `subtotal`, `timestamps`).
9. **Relasi Detail Borongan (`detail_borongans` — Tabel 14 PRD)**:
   * *PRD*: `id_detail_borongan`, `id_borongan`, `id_barang`, `jumlah_material`, `subtotal_material`.
   * *Web*: Menambahkan **`harga_satuan`** (`bigInteger`) sebagai *snapshot* harga jual saat pesanan dibuat agar riwayat rincian nota tetap akurat meskipun harga master material berubah di kemudian hari.
10. **Entitas Jadwal Pengiriman (`jadwal_pengirimen` — Tabel 10 PRD)**:
    * *PRD*: `id_jadwal`, `id_transaksi`, `id_borongan`, `alamat_pengiriman`, `tanggal_kirim`, `jam_kirim`, `biaya_pengiriman`, `status_pengiriman`.
    * *Web*: Menambahkan **`catatan_bentrok`** (`string(255)`, nullable) untuk mencatat peringatan jadwal berdekatan ≤ 1 jam yang telah dikonfirmasi pengguna; tipe `jam_kirim` disimpan sebagai `string(5)` (`HH:MM`) demi portabilitas SQLite/PostgreSQL; ditambah indeks komposit `['tanggal_kirim', 'jam_kirim']`.
11. **Entitas Penggajian Tukang (`penggajian_tukangs` — Tabel 11 PRD)**:
    * *PRD*: `id_gaji`, `id_tukang`, `id_borongan`, `tanggal_diproses`, `total_gaji`, `status_bayar`.
    * *Web*: Menambahkan **`tanggal_dibayar`** (`date`, nullable, indexed) dan **`catatan`** (`text`, nullable) untuk catatan pemilik atas upah tukang.
12. **Entitas Pengadaan (`pengadaans` — Tabel 12 PRD)** & **Detail Pengadaan (`detail_pengadaans` — Tabel 15 PRD)**:
    * *PRD*: `pengadaans` menyimpan `nama_supplier` (teks) dan tanpa tanggal terima.
    * *Web*: Mengganti `nama_supplier` dengan **`supplier_id`** (`foreignId` ke `suppliers`) dan menambahkan **`tanggal_terima`** (`date`, nullable). `detail_pengadaans` sama persis (`jumlah_pesan`, `harga_beli_satuan`, `subtotal`).

---

### 3.4 Perubahan pada Kebutuhan Non-Fungsional & Pengujian (`Bagian 3.4`–`3.5`)
* **Perlindungan Login & Sesi**: Ditambahkan *rate limiting* (`throttle:login` 10 percobaan/menit) dan regenerasi sesi saat *login*.
* **Pengalaman Pengguna (*Ergonomi*)**: Mengganti dialog konfirmasi bawaan *browser* (`alert`/`confirm`) dengan **modal konfirmasi global** berbasis Alpine.js dan mempertahankan baris input dinamis (`old()`) ketika terjadi galat validasi.
* **Verifikasi Otomatis**: Selain *Blackbox/UAT* pada PRD, seluruh logika bisnis kritis (pemotongan stok, hak akses peran, kunci status, bentrok jadwal, batasan tanggal, laporan kas, dan rendering PDF) telah diverifikasi menggunakan **31 *feature test* PHPUnit (174 *assertions*)**.

---

## Ringkasan Tindakan Jika Ingin Memperbarui Dokumen PRD
Apabila kelompok Anda hendak memperbarui Bab II dan Bab III di dokumen PRD agar 100% sinkron dengan aplikasi final:
1. Perbaiki redaksi Bab II Bagian 2.1 (*"tiga kombinasi"* → *"dua kombinasi"*), tambahkan Master Supplier pada Bagian 2.2, serta perjelas aturan bentrok 2 tingkat dan input upah manual pada Bagian 2.3.2.
2. Perbaiki kode `BRG-XXXXXX` menjadi `BRN-XXXXXX` pada Kamus Data dan Proses 2.3 di Bab III.
3. Perbarui **Gambar 2 (DFD Level 1)**, **Gambar 3 (DFD Level 2)**, **Gambar 4 (ER-Diagram)**, serta **Tabel 3–15** dengan entitas baru `Supplier` (`D13`) dan kolom tambahan (`status_karyawan`, `perlu_pengiriman`, `biaya_pengiriman`, `metode_bayar`, `status_pembayaran`, `tanggal_lunas`, `harga_satuan` pada `Detail_Borongan`, `catatan_bentrok`, `tanggal_dibayar`, `catatan`, `supplier_id`, dan `tanggal_terima`).
