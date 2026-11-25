<x-app-layout title="Laporan Operasional">
    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <form method="GET" action="{{ route('laporan.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ $dari }}" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ $sampai }}" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-semibold cursor-pointer">Tampilkan</button>
        </form>

        <a href="{{ route('laporan.pdf', ['dari' => $dari, 'sampai' => $sampai]) }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-4 py-2 text-sm">
            Unduh Laporan PDF
        </a>
    </div>

    {{-- Summary metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase">Pemasukan Lunas</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ \App\Support\Rupiah::format($totalOmzet) }}</p>
            <p class="mt-1 text-xs text-slate-500">Langsung: {{ \App\Support\Rupiah::format($totalPenjualanLangsung) }} · Borongan: {{ \App\Support\Rupiah::format($totalBorongan) }}</p>
            <p class="mt-1 text-[11px] text-amber-700">Piutang belum lunas: {{ \App\Support\Rupiah::format($totalPiutangBelumLunas) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase">Pengadaan Diterima</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ \App\Support\Rupiah::format($totalPengadaan) }}</p>
            <p class="mt-1 text-xs text-slate-500">Total dipesan (semua): {{ \App\Support\Rupiah::format($totalPengadaanSemua) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase">Pengeluaran Gaji Tukang (Dibayar)</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ \App\Support\Rupiah::format($totalGajiTukang) }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $penggajians->count() }} slip dibayar · Belum dibayar: {{ \App\Support\Rupiah::format($totalGajiBelumDibayar) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase">Material Stok Kritis</p>
            <p class="mt-2 text-xl font-bold text-rose-600">{{ $materials->filter->isStokKritis()->count() }} / {{ $materials->count() }}</p>
            <p class="mt-1 text-xs text-slate-500">Item menyentuh batas minimum</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 font-bold text-slate-900 text-sm">Rekap Penjualan &amp; Borongan Lunas</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-left font-semibold text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-2.5">Kode</th>
                            <th class="px-4 py-2.5">Pelanggan</th>
                            <th class="px-4 py-2.5">Tanggal Lunas</th>
                            <th class="px-4 py-2.5 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($penjualans as $t)
                            <tr>
                                <td class="px-4 py-2 font-mono">{{ $t->kode_transaksi }}</td>
                                <td class="px-4 py-2">{{ $t->pelanggan?->nama_pelanggan ?? 'Umum' }}</td>
                                <td class="px-4 py-2">{{ $t->tanggal_lunas?->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 text-right font-semibold">{{ \App\Support\Rupiah::format($t->total_bayar) }}</td>
                            </tr>
                        @endforeach
                        @foreach ($borongans as $b)
                            <tr>
                                <td class="px-4 py-2 font-mono">{{ $b->kode_borongan }}</td>
                                <td class="px-4 py-2">{{ $b->pelanggan->nama_pelanggan }}</td>
                                <td class="px-4 py-2">{{ $b->tanggal_lunas?->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 text-right font-semibold">{{ \App\Support\Rupiah::format($b->total_biaya) }}</td>
                            </tr>
                        @endforeach
                        @if ($penjualans->isEmpty() && $borongans->isEmpty())
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Tidak ada transaksi lunas pada periode ini.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 font-bold text-slate-900 text-sm">Laporan Sisa Stok Material</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-left font-semibold text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-2.5">Kode</th>
                            <th class="px-4 py-2.5">Material</th>
                            <th class="px-4 py-2.5 text-center">Sisa</th>
                            <th class="px-4 py-2.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($materials as $m)
                            <tr>
                                <td class="px-4 py-2 font-mono">{{ $m->kode_barang }}</td>
                                <td class="px-4 py-2 font-medium text-slate-900">{{ $m->nama_barang }}</td>
                                <td class="px-4 py-2 text-center">{{ $m->stok_sisa }} {{ $m->satuan->value }}</td>
                                <td class="px-4 py-2 text-center">
                                    <x-badge :color="$m->isStokKritis() ? 'rose' : 'emerald'">{{ $m->isStokKritis() ? 'Kritis' : 'Aman' }}</x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>

