<x-app-layout title="Dashboard Operasional">
    {{-- Quick mobile actions --}}
    <div class="grid grid-cols-2 gap-3 sm:hidden">
        <a href="{{ route('penjualan.create') }}" class="rounded-xl bg-amber-500 text-slate-950 font-semibold text-sm py-3 px-4 text-center shadow-sm">
            + Kasir (POS)
        </a>
        <a href="{{ route('borongan.create') }}" class="rounded-xl bg-slate-900 text-white font-semibold text-sm py-3 px-4 text-center shadow-sm">
            + Borongan
        </a>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Omzet Hari Ini</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ \App\Support\Rupiah::format($omzetHariIni) }}</p>
            <p class="mt-1 text-xs text-slate-500">
                Langsung: {{ \App\Support\Rupiah::format($penjualanHariIni) }} | Borongan: {{ \App\Support\Rupiah::format($boronganHariIni) }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Peringatan Stok Minimum</p>
            <p class="mt-2 text-2xl font-bold {{ $stokKritisMaterials->count() > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                {{ $stokKritisMaterials->count() }} Material
            </p>
            <p class="mt-1 text-xs text-slate-500">
                <a href="{{ route('pengadaan.index') }}" class="text-amber-600 hover:underline font-medium">Kelola pengadaan stok →</a>
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Proyek Borongan Aktif</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $boronganAktifCount }} Proyek</p>
            <p class="mt-1 text-xs text-slate-500">
                <a href="{{ route('borongan.index') }}" class="text-amber-600 hover:underline font-medium">Lihat daftar borongan →</a>
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            @if (auth()->user()->isPemilik())
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Gaji Tukang Belum Dibayar</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ \App\Support\Rupiah::format($gajiBelumDibayar) }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    <a href="{{ route('penggajian.index') }}" class="text-amber-600 hover:underline font-medium">Buka verifikasi &amp; gaji →</a>
                </p>
            @else
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Pengadaan Dalam Proses</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $pengadaanDipesanCount }} Pesanan</p>
                <p class="mt-1 text-xs text-slate-500">
                    <a href="{{ route('pengadaan.index') }}" class="text-amber-600 hover:underline font-medium">Cek penerimaan barang →</a>
                </p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Critical Stock Alerts --}}
        <section class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900">Peringatan Dini Stok Minimum</h2>
                    <p class="text-xs text-slate-500">Material dengan sisa stok &le; batas minimum (Reorder Point)</p>
                </div>
                <a href="{{ route('pengadaan.create') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">+ Pesan Stok</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($stokKritisMaterials as $item)
                    <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $item->nama_barang }}</p>
                            <p class="text-xs text-slate-500">{{ $item->kode_barang }} · {{ $item->kategori->value }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <x-badge color="rose">Sisa: {{ $item->stok_sisa }} {{ $item->satuan->value }}</x-badge>
                            <p class="text-[11px] text-slate-400 mt-1">Min: {{ $item->batas_minimum }}</p>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Semua stok material dalam kondisi aman.</p>
                @endforelse
            </div>
        </section>

        {{-- Upcoming Deliveries --}}
        <section class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900">Jadwal Pengiriman Terdekat</h2>
                    <p class="text-xs text-slate-500">Agenda pengantaran barang ke lokasi pelanggan/proyek</p>
                </div>
                <a href="{{ route('pengiriman.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">Lihat Semua</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($jadwalMendatang as $jadwal)
                    <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">
                                {{ $jadwal->kode_jadwal }} — {{ $jadwal->pesananBorongan?->kode_borongan ?? $jadwal->transaksiPenjualan?->kode_transaksi }}
                            </p>
                            <p class="text-xs text-slate-500 truncate">{{ $jadwal->alamat_pengiriman }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs font-semibold text-slate-800">{{ $jadwal->tanggal_kirim->format('d M Y') }} · {{ $jadwal->jam_kirim }}</p>
                            <x-badge :color="$jadwal->status_pengiriman === \App\Enums\StatusPengiriman::Dikirim ? 'sky' : 'amber'">
                                {{ $jadwal->status_pengiriman->value }}
                            </x-badge>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Belum ada jadwal pengiriman aktif.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Recent Direct Sales --}}
    <section class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900">Transaksi Penjualan Langsung Terbaru</h2>
            <a href="{{ route('penjualan.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Pelanggan</th>
                        <th class="px-5 py-3">Metode</th>
                        <th class="px-5 py-3 text-right">Total Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transaksiTerbaru as $trx)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-900">
                                <a href="{{ route('penjualan.show', $trx) }}" class="text-amber-600 hover:underline">{{ $trx->kode_transaksi }}</a>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $trx->tanggal->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3 text-slate-700">{{ $trx->pelanggan?->nama_pelanggan ?? 'Pelanggan Umum' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $trx->metode_bayar->value }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900">{{ \App\Support\Rupiah::format($trx->total_bayar) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada transaksi penjualan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>

