# DRAFT BAB IV & BAB V — LAPORAN FINAL PROJECT ERPIDHO
*(Disesuaikan dengan [PRD Erphido.pdf](PRD%20Erphido.pdf), template [Final Project Bab_IV_V_ERP.docx](Final%20Project%20Bab_IV_V_ERP.docx), dan implementasi aktual sistem web Laravel ERPIDHO untuk Usaha Ridho Gipsum)*

---

# BAB IV
# PERANCANGAN ANTARMUKA

## 4.1 Ruang Lingkup Kebutuhan Antarmuka
Antarmuka Sistem Informasi Enterprise ERPIDHO dirancang sebagai **Aplikasi Web Responsif (*Responsive Web Application*)** untuk mendukung operasional harian *Usaha Ridho Gipsum* baik melalui komputer kasir toko maupun *smartphone* pemilik saat berada di luar toko. Berdasarkan analisis area fungsional pada Bab II dan kebutuhan fungsional (`FR-01`–`FR-06`) pada Bab III, ruang lingkup antarmuka mencakup modul-modul berikut:

1. **Autentikasi & Data Master (`FR-01`)**: Halaman masuk (*login*) berbasis `username` dan manajemen data master (*Material*, *Pelanggan*, *Karyawan Tukang*, *Supplier*, dan *Pengguna Sistem*).
2. **Penjualan Langsung (POS) & Pesanan Paket Borongan (`FR-02`)**: Antarmuka pencatatan transaksi penjualan material langsung, pesanan paket borongan (material + biaya jasa + biaya pengiriman), pembaruan status pelunasan/proyek, serta pengunduhan nota PDF.
3. **Penjadwalan Pengiriman (`FR-03`)**: Antarmuka pembuatan jadwal antar barang/tukang dengan deteksi bentrok waktu dan pembaruan status pengiriman secara bertahap.
4. **Verifikasi Proyek & Penggajian Tukang (`FR-04`)**: Antarmuka khusus *Pemilik* untuk memverifikasi proyek selesai, menugaskan tukang secara langsung dengan nominal gaji manual serta catatan pemilik, memproses pembayaran gaji, dan mengunduh slip gaji PDF.
5. **Pengadaan Bahan & Pemantauan Stok (`FR-05`)**: Antarmuka pemantauan peringatan dini stok minimum (*reorder point*), pembuatan pesanan ke *supplier*, dan konfirmasi penerimaan barang.
6. **Dashboard & Pelaporan Operasional (`FR-06`)**: Antarmuka ringkasan operasional harian, filter laporan berbasis periode tanggal, dan ekspor laporan operasional PDF.

Sesuai batasan ruang lingkup pada PRD (Bagian 1.3 dan 1.6), antarmuka **tidak** mencakup aplikasi *mobile native* (APK/iOS), absensi digital/cuti/lembur karyawan toko, maupun modul otomasi pemasaran (*marketing automation* / promosi).

---

## 4.2 Sistem Desain dan Standar Visual
Sistem desain antarmuka dibangun menggunakan **Tailwind CSS v4** dan **Alpine.js** agar ringan dijalankan pada perangkat dengan RAM minimal 2 GB tanpa mengorbankan keterbacaan data.

1. **Tipografi dan Hierarki Data**:
   - **Antarmuka Web**: Menggunakan jenis huruf utama **`Instrument Sans`** (bobot `400`, `500`, `600`, dan `700`) untuk kejelasan label formulir dan angka transaksi, serta font *monospace* (`font-mono`) untuk kode referensi sistem (`USR-`, `BRG-`, `PLG-`, `TKG-`, `SUP-`, `TRX-`, `BRN-`, `JDW-`, `GAJ-`, `PGD-`).
   - **Dokumen Cetak PDF**: Menggunakan font **`DejaVu Sans`** agar simbol mata uang dan tata letak tabel konsisten di *DomPDF*.
   - **Hierarki Informasi**: Judul halaman diletakkan pada *header* atas (`sticky top-0`), ringkasan KPI diletakkan dalam *grid* kartu metrik di bagian atas, diikuti area aksi/filter dan tabel data utama.

2. **Palet Warna dan Makna Semantik**:
   - **Navigasi & Struktur**: Sidebar gelap **`Slate-900` (`#0F172A`)** dipadukan dengan latar konten netral **`Slate-50` (`#F8FAFC`)** dan kartu konten **`White` (`#FFFFFF`)** berbatas garis tipis `Slate-200` untuk menjaga kontras tinggi.
   - **Warna Aksen Utama**: **`Amber-500` (`#F59E0B`)** digunakan sebagai warna merek (*badge* logo `RG`), menu aktif, serta tombol aksi utama (*CTA*).
   - **Warna Status Semantik (*Badge*)**:
     - **Hijau (*Emerald*)**: Status tuntas/aman (`Lunas`, `Selesai`, `Diterima`, `Dibayar`, `Stok Aman`, `Aktif`).
     - **Kuning (*Amber*)**: Status menunggu/tindakan tertunda (`Menunggu`, `Belum Lunas`, `Terjadwal`, `Dipesan`, `Belum Dibayar`).
     - **Biru (*Sky*)**: Status sedang berjalan (`Berjalan`, `Dikirim`).
     - **Merah (*Rose*)**: Peringatan kritis atau tindakan destruktif (`Stok Kritis`, tombol `Hapus`).

3. **Dashboarding dan Responsivitas**:
   - Pada layar *desktop* (`lg`), bilah navigasi samping tampil permanen di sisi kiri.
   - Pada layar *smartphone*, navigasi berubah menjadi *slide-over drawer* melalui tombol menu di kiri atas, disertai tombol akses cepat **`+ Kasir (POS)`** dan **`+ Borongan`** tepat di bagian atas *Dashboard* agar mudah dijangkau dengan satu ibu jari saat pemilik berada di lapangan.

> **[Saran Screenshot 4.1 — Halaman Login & Dashboard Desktop/Mobile]**
> - **Gambar 4.1a**: Halaman Login (`GET /login`) menampilkan kartu masuk dengan input `username`, `password`, dan info peran.
> - **Gambar 4.1b**: Halaman Dashboard (`GET /dashboard`) sebagai akun **Pemilik** pada layar desktop yang memperlihatkan 4 kartu ringkasan (*Pemasukan Lunas Hari Ini* beserta info *Piutang Belum Lunas*, *Peringatan Stok Minimum*, *Proyek Borongan Aktif*, *Gaji Tukang Belum Dibayar*), daftar *Stok Minimum*, dan *Jadwal Pengiriman Terdekat*.
> - **Gambar 4.1c**: Tampilan Dashboard yang sama pada ukuran layar *mobile* (mode inspeksi *smartphone* ~390px) untuk menunjukkan responsivitas tombol cepat dan *sidebar drawer*.

---

## 4.3 User Flow Proses Bisnis Utama
Alur interaksi pengguna (*user flow*) pada sistem ERPIDHO dibagi berdasarkan proses inti berikut:

1. **Alur Autentikasi dan Pembatasan Menu (`FR-01`)**:
   Pengguna masuk melalui `/login` dengan `username` dan `password` → Sistem memvalidasi kredensial dan peran (`Pemilik` atau `Keluarga`) → Pengguna diarahkan ke `/dashboard` dengan menu *sidebar* yang otomatis menyesuaikan hak akses perannya.
2. **Alur Penjualan Langsung / POS (`FR-02`)**:
   Buka **Penjualan Langsung** → Klik **`+ Transaksi Baru`** → Pilih pelanggan lama, pilih *Pelanggan Umum*, atau tambah *Pelanggan Baru* secara *inline* → Tambahkan baris material dan jumlah → Centang *"Perlu Pengiriman?"* jika perlu antar dan isi biaya kirim → Pilih metode bayar, status (`Lunas`/`Belum Lunas`), dan `tanggal_lunas` → Simpan (stok terpotong otomatis) → Buka detail transaksi untuk mengunduh **Nota Penjualan PDF** atau lanjut membuat jadwal kirim.
3. **Alur Pesanan Paket Borongan (`FR-02`)**:
   Buka **Pesanan Borongan** → Klik **`+ Pesanan Borongan`** → Pilih atau buat pelanggan (wajib) → Isi `tanggal_mulai` (`>= hari ini`), `tanggal_target`, `lokasi_proyek`, `biaya_jasa`, `biaya_pengiriman`, dan status pembayaran → Alokasikan baris material → Simpan (status proyek `Menunggu`, stok terpotong otomatis) → Perbarui status maju (`Menunggu → Berjalan → Selesai`) dan pelunasan (`Belum Lunas → Lunas`) melalui modal konfirmasi → Unduh **Nota Borongan PDF**.
4. **Alur Penjadwalan Pengiriman (`FR-03`)**:
   Buka **Jadwal Pengiriman** → Klik **`+ Jadwalkan Pengiriman`** → Pilih jenis pesanan (*Borongan* atau *POS yang perlu pengiriman*) → Sistem menampilkan `biaya_pengiriman` yang sudah tercatat pada pesanan secara *read-only* dan mengisi otomatis alamat tujuan → Pilih `tanggal_kirim` (`>= hari ini`) dan `jam_kirim` → Jika tepat sama dengan jadwal aktif lain, sistem menolak (*hard block*); jika selisih ≤ 1 jam, sistem menampilkan peringatan dan meminta centang konfirmasi → Simpan → Pada daftar jadwal, gunakan tombol bertahap **`Mulai Kirim`** dan **`Tandai Selesai`** melalui modal konfirmasi.
5. **Alur Pemantauan Stok & Pengadaan (`FR-05`)**:
   Buka **Pengadaan & Stok** → Sistem menyorot daftar material kritis (`stok_sisa <= batas_minimum`) → Klik **`+ Buat Pengadaan Stok Kritis`** (otomatis mengisi baris material kritis) atau **`+ Buat Pengadaan Baru`** → Pilih `Supplier` dan jumlah pesan → Simpan (`Dipesan`) dan unduh PDF daftar pesanan → Saat barang datang, buka detail pengadaan dan klik **`Konfirmasi Barang Diterima (+ Stok)`** melalui modal konfirmasi → Status berubah menjadi `Diterima` dan `stok_sisa` bertambah otomatis.
6. **Alur Verifikasi Proyek & Penggajian Tukang (`FR-04`, Khusus Pemilik)**:
   Buka **Verifikasi & Gaji Tukang** → Pilih `Pesanan Borongan` dan `tanggal_diproses` → Tambahkan satu atau beberapa `Karyawan Tukang`, isi `total_gaji` manual, `status_bayar`, `tanggal_dibayar` (jika langsung dibayar), dan `catatan` opsional → Simpan (proyek otomatis terverifikasi `Selesai`) → Untuk slip yang masih `Belum Dibayar`, pilih tanggal bayar dan klik **`Tandai Dibayar`** melalui modal konfirmasi → Unduh **Slip Gaji PDF**.
7. **Alur Pelaporan Operasional (`FR-06`, Khusus Pemilik)**:
   Buka **Laporan Operasional** → Pilih rentang tanggal (`Dari`–`Sampai`) → Sistem menampilkan rekap *Pemasukan Lunas* (berdasarkan `tanggal_lunas`), *Pengadaan Diterima* (berdasarkan `tanggal_terima`), *Pengeluaran Gaji Tukang Dibayar* (berdasarkan `tanggal_dibayar`), serta sisa stok material → Klik **`Unduh Laporan PDF`**.

> **[Saran Screenshot 4.2 — Alur Halaman Utama]**
> - Tangkap layar halaman **Detail Borongan** (`borongan/show`) atau **Penjualan POS** (`penjualan/create`) yang menunjukkan interaksi pengisian form dinamis dan tombol PDF.

---

## 4.4 Aksesibilitas (Kepatuhan Standar WCAG 2.1 Level AA)
Implementasi antarmuka ERPIDHO memperhatikan prinsip aksesibilitas web (**WCAG 2.1 Level AA**) melalui praktik berikut:
1. **Kontras Warna (*Contrast Ratio*)**: Penggunaan teks `Slate-900`/`Slate-700` di atas latar `White`/`Slate-50`, serta teks putih/hitam kontras pada tombol `Emerald-600`, `Rose-600`, `Slate-900`, dan `Amber-500` agar tetap terbaca jelas di bawah pencahayaan luar ruangan saat pemilik membuka melalui *smartphone*.
2. **Struktur Semantik HTML5**: Penggunaan elemen semantik `<aside>`, `<nav>`, `<header>`, `<main>`, `<section>`, serta struktur `<thead>` dan `<tbody>` pada seluruh tabel data.
3. **Aksesibilitas Dialog & Keyboard**:
   - Modal konfirmasi global menggunakan atribut `role="dialog"`, `aria-modal="true"`, dan `aria-labelledby="confirm-modal-title"`.
   - Pengguna dapat menutup modal dengan tombol **Batal**, mengklik area luar modal, atau menekan tombol **`Escape`** pada *keyboard*.
   - Tombol buka/tutup menu pada tampilan *mobile* dilengkapi `aria-label="Buka menu"` dan `aria-label="Tutup menu"`.
4. **Keterhubungan Label & Umpan Balik Error**: Setiap *input* form memiliki `<label>` eksplisit dan pesan error validasi ditampilkan secara terstruktur di bagian atas halaman dalam bahasa Indonesia yang jelas.

---

## 4.5 Komponen UI yang Digunakan dalam Pengembangan ERP
Seluruh antarmuka disusun secara modular menggunakan komponen Blade dan Alpine.js berikut:
1. **Navbar / Header & Responsive Sidebar (`layouts/app.blade.php`)**: Berisi identitas aplikasi (`ERPIDHO`), menu navigasi yang dikelompokkan berdasarkan area operasional dan hak akses peran, indikator jumlah stok kritis (*badge* merah pada menu *Pengadaan & Stok*), informasi pengguna aktif, serta tombol aksi cepat.
2. **Summary Stat Cards**: Kartu ringkasan 4 kolom pada *Dashboard* dan *Laporan Operasional* untuk menampilkan metrik kunci (omzet/pemasukan lunas, stok kritis, proyek aktif, piutang, dan pengeluaran).
3. **Data Tables & Filter Bar**: Tabel responsif (`overflow-x-auto`) dilengkapi input pencarian, *dropdown* filter status, serta kontrol paginasi Laravel.
4. **Dynamic Multi-Item Form (`Alpine.js`)**: Digunakan pada form *POS*, *Borongan*, *Pengadaan*, dan *Penggajian* untuk menambah/menghapus baris material atau tukang secara *real-time* beserta kalkulasi subtotal otomatis dan pemulihan nilai `old()` jika validasi gagal.
5. **Status Badges (`components/badge.blade.php`) & Alert Banners**: Komponen *badge* berwarna semantik serta *banner* peringatan stok kritis (`bg-rose-50`) dan peringatan jadwal berdekatan (`bg-amber-50`).
6. **Global Confirmation Modal (`layouts/app.blade.php` & `resources/js/app.js`)**: Menggantikan pop-up `confirm()` bawaan *browser* pada seluruh aksi krusial (pelunasan, perubahan status proyek/pengiriman, penerimaan barang, pembayaran gaji, dan penghapusan data master) dengan variasi warna dan teks kontekstual sesuai aksi.
7. **PDF Document Templates (`resources/views/pdf/*.blade.php`)**: Templat laporan dan nota siap cetak (*Nota Penjualan*, *Nota Borongan*, *Slip Gaji Tukang*, *Daftar Pesanan Pengadaan*, dan *Laporan Operasional*).

> **[Saran Screenshot 4.3 — Komponen Modal Konfirmasi & Nota PDF]**
> - **Gambar 4.3a**: Tampilan **Modal Konfirmasi Global** yang sedang terbuka saat mengubah status proyek (`BRN-000003`) atau saat menekan **Tandai Dibayar** pada halaman penggajian.
> - **Gambar 4.3b**: Cuplikan hasil render dokumen PDF (misalnya **Nota Borongan PDF** atau **Slip Gaji PDF**).

---

## 4.6 Desain Antarmuka Modul CRM (Pengelolaan Pelanggan & Layanan Pesanan)
*(Disesuaikan dengan area fungsional Usaha Ridho Gipsum dari PRD Bagian 1.3 & 2.2)*

1. **Deskripsi Antarmuka**:
   Antarmuka pengelolaan pelanggan berpusat pada halaman **Data Pelanggan** (`/pelanggans`) yang menampilkan tabel `kode_pelanggan` (`PLG-XXXX`), nama, nomor telepon, dan alamat beserta fitur pencarian cepat dan form tambah/ubah pelanggan. Selain itu, antarmuka CRM terintegrasi langsung ke dalam form **Penjualan Langsung** dan **Pesanan Borongan** sehingga pengelola dapat memilih pelanggan terdaftar atau mendaftarkan pelanggan baru tanpa berpindah halaman.
2. **Inovasi**:
   - **Inline Quick-Create Pelanggan pada Kasir & Borongan**: Memangkas waktu pencatatan di toko dengan opsi beralih instan antara mode *"Pilih Pelanggan"* (termasuk *Pelanggan Umum* untuk pembelian cepat) dan *"+ Pelanggan Baru"* langsung di layar transaksi.
3. **Report & KPI yang Ditampilkan**:
   - Daftar riwayat transaksi dan pesanan borongan per pelanggan melalui fitur pencarian nama pelanggan di modul Penjualan & Borongan.
   - **KPI Piutang Pelanggan Belum Lunas (`totalPiutangBelumLunas`)**: Ditampilkan pada kartu *Dashboard* dan *Laporan Operasional* untuk memantau total tagihan pelanggan yang belum dilunasi.
   - *(Catatan cakupan: KPI lead pipeline, konversi prospek, dan efektivitas promo tidak disertakan karena di luar kebutuhan bisnis UMKM sesuai PRD Bagian 1.3).*

> **[Saran Screenshot 4.4 — Antarmuka Pelanggan (CRM)]**
> - Halaman **Data Pelanggan** (`/pelanggans`) yang menampilkan daftar pelanggan beserta kolom pencarian, dan/atau bagian *toggle* **Pilih Pelanggan / + Pelanggan Baru** pada form **Pesanan Borongan**.

---

## 4.7 Desain Antarmuka Modul SCM (Persediaan, Pengadaan & Pengiriman)
1. **Deskripsi Antarmuka**:
   Modul SCM terbagi atas tiga antarmuka yang saling terhubung:
   - **Data Material (`/materials`)**: Menampilkan stok aktual (`stok_sisa`), batas minimum, harga beli, harga jual, serta filter *Stok Kritis*.
   - **Pengadaan & Stok (`/pengadaan`)**: Menampilkan panel peringatan merah untuk seluruh material dengan `stok_sisa <= batas_minimum`, tabel riwayat pengadaan ke **Master Supplier** (`/suppliers`), detail pesanan, dan tombol konfirmasi penerimaan barang.
   - **Jadwal Pengiriman (`/pengiriman`)**: Menampilkan agenda pengantaran aktif dengan status bertahap (`Terjadwal`, `Dikirim`, `Selesai`) serta form penjadwalan untuk *Pesanan Borongan* maupun *Penjualan POS* yang membutuhkan antar.
2. **Inovasi**:
   - **One-Click Pre-fill Pengadaan Stok Kritis**: Tombol **`+ Buat Pengadaan Stok Kritis`** otomatis mengisi daftar material yang berada di bawah batas minimum beserta rekomendasi jumlah pesan dan harga beli terakhir.
   - **Two-Tier Delivery Conflict Guard**: Sistem otomatis memblokir jadwal yang bentrok tepat pada tanggal dan jam yang sama dengan alasan konflik spesifik, serta memunculkan *banner* peringatan + kotak centang konfirmasi apabila jadwal baru berjarak ≤ 1 jam dari pengiriman lain.
   - **Pemisahan Biaya Pengiriman dan Jadwal Logistik**: Biaya antar ditetapkan saat transaksi/borongan agar total nota konsisten, sementara modul jadwal pengiriman berfokus penuh pada koordinasi waktu dan alamat antar.
3. **Report & KPI yang Ditampilkan**:
   - **KPI Peringatan Stok Minimum**: Jumlah material berstatus *Kritis* secara *real-time* (pada *badge sidebar*, kartu *Dashboard*, dan panel atas *Pengadaan*).
   - **Laporan Sisa Stok Material** (Web & PDF): Daftar lengkap seluruh material beserta status `Aman` atau `Kritis`.
   - **KPI Pengadaan Bahan**: Total nilai *Pengadaan Diterima* berdasarkan `tanggal_terima` pada periode laporan, dibandingkan dengan *Total Dipesan* berdasarkan `tanggal_pesan`.
   - **KPI Pengiriman**: Daftar jadwal pengiriman terdekat pada *Dashboard* dan pelacakan status pengiriman.

> **[Saran Screenshot 4.5 — Antarmuka SCM (Pengadaan, Stok Kritis & Jadwal Pengiriman)]**
> - **Gambar 4.5a**: Halaman **Pengadaan & Stok** (`/pengadaan`) dalam keadaan memiliki material stok kritis (menampilkan panel peringatan merah + tombol `+ Buat Pengadaan Stok Kritis`).
> - **Gambar 4.5b**: Halaman **Buat Jadwal Pengiriman** (`/pengiriman/create`) saat menampilkan pesan error bentrok waktu atau kotak peringatan kuning **Selisih Waktu ≤ 1 Jam** beserta centang konfirmasi.

---

## 4.8 Desain Antarmuka Modul HRM (Pengelolaan & Penggajian Karyawan Tukang)
*(Disesuaikan dengan cakupan tenaga kerja lapangan pada Usaha Ridho Gipsum dari PRD Bagian 1.3 & FR-04)*

1. **Deskripsi Antarmuka**:
   Modul ini terdiri atas halaman master **Karyawan Tukang** (`/tukangs`) yang menyimpan kode tukang (`TKG-XXX`), nama, nomor telepon, dan status keaktifan (`Aktif`/`Nonaktif`), serta halaman terpadu **Verifikasi Proyek & Penggajian Tukang** (`/penggajian`). Pada halaman penggajian (khusus *Pemilik*), sisi kiri berisi form verifikasi proyek selesai dan penugasan tukang, sedangkan sisi kanan berisi kartu total gaji tertunggak dan tabel rekap slip gaji.
2. **Inovasi**:
   - **Direct Payroll Assignment & Catatan Pemilik**: Karena pekerja lapangan tidak menggunakan tarif tetap tunggal, tukang ditugaskan langsung saat verifikasi proyek dengan input `total_gaji` manual per orang serta kolom **`catatan`** opsional (misalnya keterangan lembur, bonus target, atau kasbon).
   - **Pencatatan Cash-Basis Fleksibel (`tanggal_dibayar`) & Kunci Status**: Pemilik dapat memasukkan `tanggal_dibayar` secara mundur (*backdate*) jika baru sempat mencatat pembayaran kas hari sebelumnya, dan setelah ditandai `Dibayar`, status terkunci permanen beserta fitur unduh **Slip Gaji PDF** per tukang.
3. **Report & KPI yang Ditampilkan**:
   - **KPI Gaji Tukang Belum Dibayar**: Ditampilkan pada kartu *Dashboard* (khusus *Pemilik*) dan *header* halaman Penggajian.
   - **KPI Pengeluaran Gaji Tukang (Dibayar)**: Ditampilkan pada **Laporan Operasional** berdasarkan `tanggal_dibayar` pada periode yang dipilih, disertai informasi kewajiban gaji yang masih *Belum Dibayar*.
   - **Slip Gaji PDF Individual**: Dokumen pertanggungjawaban upah per tukang per proyek borongan.
   - *(Catatan cakupan: Fitur cuti, lembur reguler, dan presensi harian karyawan toko tidak dibuat karena toko dikelola mandiri oleh pemilik dan keluarga per PRD Bagian 1.1 & 1.3).*

> **[Saran Screenshot 4.6 — Antarmuka Penggajian Tukang (HRM)]**
> - Halaman **Verifikasi Proyek & Penggajian Tukang** (`/penggajian`) yang memperlihatkan form penugasan multi-tukang dengan input `catatan` di kiri, dan tabel rekap di kanan yang menunjukkan perbedaan baris `Belum Dibayar` (memiliki *date picker* + tombol hijau **Tandai Dibayar**) dan baris `Dibayar` (*badge* hijau terkunci + tautan **Slip PDF**).

---

## 4.9 Desain Antarmuka Modul Sales / POS (Penjualan Langsung & Pesanan Borongan)
1. **Deskripsi Antarmuka**:
   Modul penjualan memisahkan secara sistemis antara **Penjualan Langsung (POS)** (`/penjualan`) untuk pembelian material eceran/kasir dan **Pesanan Paket Borongan** (`/borongan`) untuk proyek pemasangan lengkap. Kedua modul memiliki halaman daftar dengan filter pencarian & status, halaman form input dinamis berhitung otomatis, serta halaman detail yang menampilkan rincian material, status pembayaran, kontrol status, tautan penjadwalan kirim, dan tombol unduh **Nota PDF**.
2. **Inovasi**:
   - **Pemisahan Transaksi Otomatis & Kalkulasi Real-Time**: Kasir tidak perlu menghitung subtotal maupun menjumlah biaya jasa dan ongkos kirim secara manual karena Alpine.js menghitung total secara langsung, sementara sisi *server* memotong stok material secara atomik.
   - **Opsi Pengiriman Terintegrasi di Kasir**: Pembelian langsung menyediakan sakelar *"Perlu Pengiriman ke Lokasi Pelanggan?"* sehingga biaya antar langsung masuk ke nota penjualan sebelum pelunasan.
   - **Kontrol Status Proyek & Pelunasan Berbasis Modal**: Peralihan status proyek (`Menunggu → Berjalan → Selesai`) maupun pelunasan (`Belum Lunas → Lunas`) menggunakan modal konfirmasi dinamis yang menyesuaikan isi pesan dengan pilihan pengguna.
3. **Report & KPI yang Ditampilkan**:
   - **KPI Pemasukan Lunas Hari Ini** (*Dashboard*): Menjumlahkan penjualan langsung dan borongan yang berstatus `Lunas` dengan `tanggal_lunas` hari ini.
   - **Laporan Penjualan & Borongan Lunas** (*Laporan Operasional* Web & PDF): Merinci transaksi `Lunas` dalam rentang `tanggal_lunas`, memisahkan nilai Penjualan Langsung vs. Borongan, serta menampilkan total **Piutang Belum Lunas** berjalan.

> **[Saran Screenshot 4.7 — Antarmuka POS & Laporan Operasional]**
> - **Gambar 4.7a**: Halaman **Buat Transaksi Penjualan Langsung** (`/penjualan/create`) dengan beberapa baris material, kotak opsi *Perlu Pengiriman* tercentang, dan panel total hitam-amber di bagian bawah.
> - **Gambar 4.7b**: Halaman **Laporan Operasional** (`/laporan`) yang memperlihatkan filter periode, 4 kartu KPI keuangan & stok kritis, serta dua tabel rekap (*Rekap Penjualan & Borongan Lunas* dan *Laporan Sisa Stok Material*).

---
---

# BAB V
# PERANCANGAN JAMINAN KEAMANAN DAN KENDALI INTERNAL ERP

## 5.1 Penerapan Segregation of Duties (SoD) Berdasarkan User Role
Untuk meminimalkan risiko penyalahgunaan wewenang dan kesalahan pencatatan data sensitif, ERPIDHO menerapkan pemisahan tugas (*Segregation of Duties*) berbasis peran menggunakan *middleware* `EnsureUserHasRole` (`role:Pemilik`) pada [routes/web.php](routes/web.php) serta pemeriksaan otorisasi di level *controller*. Terdapat dua peran pengguna: **`Pemilik`** dan **`Keluarga`**.

| Fitur / Aktivitas Sistem | Peran `Keluarga` | Peran `Pemilik` | Tujuan Kendali SoD |
| :--- | :---: | :---: | :--- |
| Dashboard Operasional & Daftar Material | Diizinkan | Diizinkan | Memantau stok dan jadwal harian toko |
| Tambah / Ubah / Hapus Data Material (Harga & Min. Stok) | Ditolak (`403`) | Diizinkan | Mencegah manipulasi harga jual/beli & batas stok |
| Manajemen Data Pelanggan (`PLG-`) | Diizinkan | Diizinkan | Mendukung kelancaran transaksi kasir & borongan |
| Manajemen Tukang (`TKG-`), Supplier (`SUP-`), & Pengguna (`USR-`) | Ditolak (`403`) | Diizinkan | Melindungi data tenaga kerja, mitra, & akun akses |
| Buat Transaksi POS, Pesanan Borongan, & Jadwal Kirim | Diizinkan | Diizinkan | Operasional kasir & pengiriman tetap berjalan saat pemilik di luar |
| Tandai Pelunasan (`Belum Lunas → Lunas`) | Diizinkan | Diizinkan | Mengonfirmasi penerimaan pembayaran di toko |
| Batalkan Pelunasan (`Lunas → Belum Lunas`) | Ditolak (`403`) | Diizinkan | Mencegah penggelapan/penghapusan status lunas oleh kasir |
| Ubah Status Borongan `Menunggu → Berjalan` | Diizinkan | Diizinkan | Mencatat dimulainya pekerjaan lapangan |
| Verifikasi Proyek `Selesai` & Penggajian Tukang (`FR-04`) | Ditolak (`403`) | Diizinkan | Memastikan verifikasi proyek & pengeluaran upah hanya oleh pemilik |
| Buat Pengadaan & Konfirmasi Barang Diterima (`FR-05`) | Diizinkan | Diizinkan | Mendukung penerimaan stok fisik di toko secara tepat waktu |
| Akses Laporan Operasional & Ekspor PDF (`FR-06`) | Ditolak (`403`) | Diizinkan | Menjaga kerahasiaan omzet, pengeluaran, dan keuangan usaha |

---

## 5.2 Mekanisme Mitigasi Kesalahan Input dan Validasi Data
Sistem menerapkan validasi berlapis di sisi *client* (HTML5/Alpine) dan sisi *server* (*Laravel Validation* + transaksi basis data) untuk menjaga integritas data:

1. **Perlindungan Integritas Stok & Pencegahan *Race Condition***:
   - Seluruh pemotongan stok pada penjualan langsung maupun borongan dipusatkan pada `StokService::deductMaterials()` di dalam `DB::transaction()` menggunakan **`lockForUpdate()`** pada baris `materials`.
   - Sistem menggabungkan kuantitas jika material yang sama muncul di beberapa baris dan menolak transaksi apabila `stok_sisa < jumlah_diminta`, sehingga stok mustahil bernilai negatif.
   - Konfirmasi penerimaan pengadaan (`PengadaanController::receive()`) juga menggunakan `lockForUpdate()` untuk mencegah penambahan stok ganda apabila tombol ditekan dua kali secara bersamaan.
2. **Pencegahan Duplikasi Data Master & Transaksi**:
   - Validasi `unique` pada `username` pengguna; validasi **`distinct`** pada pemilihan `material_id` saat pengadaan (`PengadaanController`) dan pemilihan `karyawan_tukang_id` saat penggajian (`PenggajianTukangController`), ditambah pengecekan *database* agar satu tukang tidak menerima dua slip gaji untuk proyek borongan yang sama.
   - Pencegahan pembuatan dua `JadwalPengiriman` aktif untuk satu kode transaksi atau borongan yang sama.
3. **Validasi Rentang Waktu & Logika Kalender**:
   - **Jadwal ke depan tidak boleh di masa lalu**: `tanggal_kirim >= today` (dan `jam_kirim >= waktu saat ini` jika dijadwalkan hari ini), `tanggal_mulai >= today`, serta `tanggal_target >= tanggal_mulai`.
   - **Catatan transaksi/pembayaran historis tidak boleh di masa depan**: `tanggal_lunas`, `tanggal_diproses`, `tanggal_dibayar`, dan `tanggal_pesan` dibatasi `before_or_equal:today` agar tetap mendukung pencatatan susulan (*catch-up*) tanpa risiko salah ketik tanggal masa depan.
4. **Validasi Numerik, Format, dan Proteksi Penghapusan**:
   - Seluruh harga, jumlah item, biaya jasa, biaya kirim, dan nominal gaji divalidasi `integer` dengan batas `min:0` atau `min:1`. Nomor telepon divalidasi dengan pola angka/`+` (`regex:/^[0-9+]+$/`).
   - Penghapusan data master (`Material`, `Pelanggan`, `KaryawanTukang`, `Supplier`, `User`) diblokir apabila data sudah terikat pada riwayat transaksi, pengadaan, atau penggajian; pengguna juga dicegah menghapus akunnya sendiri yang sedang aktif.
   - Form dinamis mempertahankan input `old()` jika validasi gagal agar pengelola tidak perlu mengetik ulang seluruh baris transaksi.

---

## 5.3 Aturan Override dan Prosedur Persetujuan (Approval Workflow)
Untuk aktivitas yang berdampak finansial maupun operasional krusial, ERPIDHO menerapkan mekanisme persetujuan (*approval*), kunci status satu arah (*one-way lock*), dan aturan *override* terkontrol:

1. **Kunci Status Satu Arah (*Immutable Completed States*)**:
   - **Penggajian Tukang (`Belum Dibayar → Dibayar`)**: Setelah pemilik mengonfirmasi pembayaran dan `tanggal_dibayar` disimpan, status terkunci permanen dan *controller* menolak setiap percobaan mengembalikannya ke `Belum Dibayar` untuk menjaga akurasi laporan kas dan mencegah pembayaran ganda.
   - **Pengadaan Bahan (`Dipesan → Diterima`)**: Status penerimaan hanya dapat bergerak maju satu kali; setelah stok bertambah, pesanan pengadaan terkunci.
   - **Proyek Borongan (`Menunggu → Berjalan → Selesai`)**: Status hanya bergerak maju. Status `Selesai` hanya dapat diverifikasi oleh `Pemilik` (baik langsung di detail borongan maupun otomatis saat memproses penggajian tukang) dan bersifat final/terkunci.
   - **Jadwal Pengiriman (`Terjadwal → Dikirim → Selesai`)**: Harus melalui tahapan berurutan tanpa melompati tahap atau mundur kembali setelah `Selesai`.
2. **Prosedur *Override* Khusus `Pemilik`**:
   - Pada kasus koreksi pelunasan transaksi (`TransaksiPenjualan` dan `PesananBorongan`), peralihan dari **`Lunas` kembali ke `Belum Lunas`** dikunci bagi `Keluarga` (`403 Forbidden`), namun diizinkan sebagai hak *override* eksklusif **`Pemilik`** untuk mengoreksi kesalahan pencatatan kasir.
3. **Prosedur *Override* Peringatan Jadwal Pengiriman**:
   - Bentrokan jadwal pada tanggal dan jam yang persis sama diblokir mutlak. Namun untuk jadwal berdekatan dalam rentang **±1 jam**, sistem menampilkan peringatan detail jadwal terkait dan mengizinkan *override* hanya jika pengelola secara sadar mencentang konfirmasi persetujuan, yang kemudian direkam permanen pada kolom `catatan_bentrok`.
4. **Konfirmasi Modal Eksplisit**:
   - Seluruh aksi persetujuan status, pelunasan, penerimaan stok, pembayaran gaji, dan penghapusan data mewajibkan konfirmasi ulang melalui modal dialog kontekstual sebelum permintaan `POST`/`PATCH`/`DELETE` dikirim ke *server*.

---

## 5.4 Perancangan Pengamanan Akses, Sesi, dan Error Handling
*(Disesuaikan dengan arsitektur web server-rendered Laravel ERPIDHO; apabila ke depan dikembangkan API eksternal/mobile, autentikasi token seperti Laravel Sanctum dapat ditambahkan di atas lapisan ini).*

1. **Pengamanan Autentikasi & Sesi**:
   - Kata sandi seluruh pengguna di-*hash* satu arah (`password => hashed` menggunakan Bcrypt melalui konfigurasi `BCRYPT_ROUNDS`).
   - Rute `POST /login` dilindungi pembatasan frekuensi permintaan (*rate limiting*) melalui `RateLimiter::for('login')` maksimal **10 percobaan per menit** per kombinasi `username|IP` untuk mencegah serangan *brute-force*.
   - Setiap proses *login* berhasil memanggil `$request->session()->regenerate()` untuk mencegah serangan *session fixation*, dan proses *logout* menghapus sesi serta merotasi token CSRF.
2. **Proteksi Serangan Web (*CSRF, XSS, & SQL Injection*)**:
   - Seluruh form pengubah state wajib menyertakan token **`@csrf`**.
   - Seluruh keluaran Blade menggunakan sintaks **`{{ ... }}`** yang otomatis melakukan *HTML-escaping* guna mencegah *Cross-Site Scripting (XSS)*.
   - Seluruh kueri basis data menggunakan **Eloquent ORM** dan *parameter binding* (tanpa interpolasi string SQL mentah), serta kolom mass-assignment dibatasi melalui properti `$fillable` pada setiap model.
   - Pada lingkungan produksi (*Cloud VPS*), jalur komunikasi diamankan menggunakan sertifikat **SSL (HTTPS)**.
3. **Penanganan Kesalahan (*Error Handling*)**:
   - Kesalahan validasi bisnis (stok kurang, bentrok jadwal, duplikasi tukang/material, status terkunci) ditangkap secara terstruktur sebagai `ValidationException` dengan pesan berbahasa Indonesia yang informatif tanpa membocorkan detail teknis basis data.
   - Pelanggaran otorisasi peran mengembalikan respons **`HTTP 403 Forbidden`**, dan referensi ID yang tidak valid mengembalikan **`HTTP 404 Not Found`**.

---

## 5.5 Rancangan Kendali Internal untuk Modul CRM (Pelanggan)
Kendali internal pada modul pengelolaan pelanggan meliputi:
1. **Identifikasi Unik & Pencegahan Redundansi**: Setiap pelanggan diberi kode otomatis berurutan (`PLG-XXXX`), dengan dukungan memilih pelanggan terdaftar saat transaksi agar tidak terjadi pembuatan duplikasi pelanggan secara tidak sengaja.
2. **Pemisahan Wajib vs. Opsional Data Pelanggan**: Pembelian langsung di toko mengizinkan mode *Pelanggan Umum*, sedangkan **Pesanan Borongan mewajibkan data pelanggan** (`required_without:pelanggan_id`) karena terkait tanggung jawab pengerjaan proyek di lokasi pelanggan.
3. **Proteksi Integritas Riwayat**: Pelanggan yang sudah memiliki catatan `TransaksiPenjualan` atau `PesananBorongan` tidak dapat dihapus (`PelangganController::destroy`) agar jejak nota dan piutang tetap utuh.

---

## 5.6 Rancangan Kendali Internal untuk Modul SCM (Stok, Pengadaan & Pengiriman)
Kendali internal pada rantai pasok toko meliputi:
1. **Validasi Stok Atomik Saat Penjualan**: Pemotongan stok hanya terjadi di dalam transaksi database yang mengunci baris material (`lockForUpdate`), mencegah penjualan fiktif melebihi stok fisik gudang.
2. **Kendali Pengadaan Bahan (`Master Supplier`)**: Pemesanan wajib merujuk pada `Supplier` terdaftar, melarang duplikasi item material dalam satu PO (`distinct`), dan penambahan `stok_sisa` hanya dipicu ketika status berubah dari `Dipesan` menjadi `Diterima` dengan penguncian baris agar tidak terjadi penambahan stok ganda.
3. **Kendali Pengiriman & Bentrok Waktu**: Satu pesanan hanya dapat memiliki satu jadwal aktif; bentrok waktu persis diblokir secara sistemis; selisih ≤ 1 jam wajib melalui konfirmasi *override* tercatat; biaya pengiriman dikunci sejak order dibuat; dan status pengiriman berjalan searah hingga terkunci di `Selesai`.

---

## 5.7 Rancangan Kendali Internal untuk Modul HRM (Karyawan Tukang & Penggajian)
Kendali internal pada pengelolaan tenaga kerja lapangan meliputi:
1. **Otorisasi Eksklusif Pemilik**: Hanya `Pemilik` yang dapat menambah/mengubah data `KaryawanTukang`, memverifikasi proyek borongan selesai, memproses gaji, dan mengunduh slip gaji.
2. **Validasi Kelayakan & Anti-Duplikasi Penggajian**: Hanya tukang berstatus `Aktif` yang muncul dalam pilihan penugasan; sistem menolak duplikasi tukang pada proyek yang sama (`distinct` + pengecekan tabel `penggajian_tukangs`).
3. **Imutabilitas Slip Dibayar & Akurasi Kas**: Status `Dibayar` mengunci catatan gaji secara permanen dengan `tanggal_dibayar` (`<= hari ini`), memastikan hanya upah yang benar-benar telah dibayarkan yang tercatat sebagai pengeluaran pada *Laporan Operasional*, sementara nilai yang belum dibayar tetap terpantau sebagai kewajiban di *Dashboard*.

---

## 5.8 Rancangan Kendali Internal untuk Modul Sales / POS (Penjualan & Borongan)
Kendali internal pada aktivitas penjualan meliputi:
1. **Integritas Harga & Kalkulasi Server-Side**: Meskipun antarmuka menghitung estimasi total secara *real-time* via Alpine.js, seluruh `harga_satuan`, `subtotal`, dan `total_bayar`/`total_biaya` **dihitung ulang di sisi server** berdasarkan harga jual resmi dari tabel `materials` (mencegah manipulasi harga dari sisi *browser*).
2. **Pemisahan Alur & Proteksi Pelunasan**: Penjualan eceran (`TRX-`) dan proyek borongan (`BRN-`) tercatat pada entitas terpisah dengan nota PDF masing-masing. Hanya `Lunas` yang masuk sebagai pemasukan keuangan berdasarkan `tanggal_lunas`, dan pembatalan status `Lunas → Belum Lunas` hanya boleh dilakukan oleh `Pemilik`.
3. **Penguncian Penyelesaian Proyek**: Proyek borongan tidak dapat ditandai `Selesai` oleh peran `Keluarga` dan tidak dapat dimundurkan statusnya setelah diverifikasi `Selesai`.

---

## Lampiran Praktis: Daftar Screenshot yang Perlu Diambil dari Aplikasi
*(Jalankan aplikasi lokal lalu login sebagai `pemilik` / `password` untuk mengambil tangkapan layar berikut sebelum memindahkannya ke `.docx`)*:

1. **Gambar 4.1a** — Halaman Login (`/login`).
2. **Gambar 4.1b** — Dashboard Pemilik versi Desktop (`/dashboard`).
3. **Gambar 4.1c** — Dashboard versi Mobile (~390px via DevTools Chrome) dengan menu *drawer* terbuka/tertutup.
4. **Gambar 4.3a** — Modal Konfirmasi Global sedang terbuka (misalnya pada `/borongan/{id}` saat klik *Perbarui Status* atau pada `/penggajian` saat klik *Tandai Dibayar*).
5. **Gambar 4.3b** — Contoh PDF yang sudah dibuka di browser (Nota Borongan PDF atau Slip Gaji PDF).
6. **Gambar 4.4** — Halaman Data Pelanggan (`/pelanggans`).
7. **Gambar 4.5a** — Halaman Pengadaan & Stok (`/pengadaan`) yang menampilkan kotak merah *Peringatan Stok Minimum*.
8. **Gambar 4.5b** — Halaman Buat Jadwal Pengiriman (`/pengiriman/create`) yang menampilkan error bentrok waktu atau peringatan kuning ±1 jam.
9. **Gambar 4.6** — Halaman Verifikasi Proyek & Penggajian Tukang (`/penggajian`).
10. **Gambar 4.7a** — Halaman Buat Penjualan Langsung (`/penjualan/create`) dengan opsi *Perlu Pengiriman* tercentang.
11. **Gambar 4.7b** — Halaman Laporan Operasional (`/laporan`).
