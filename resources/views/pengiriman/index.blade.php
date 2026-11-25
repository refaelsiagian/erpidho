<x-app-layout title="Jadwal Pengiriman Barang">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('pengiriman.index') }}" class="flex items-center gap-2">
            <select name="status" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach ($statuses as $st)
                    <option value="{{ $st->value }}" @selected($selectedStatus === $st->value)>{{ $st->value }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-medium cursor-pointer">Filter</button>
        </form>
        <a href="{{ route('pengiriman.create') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-4 py-2 text-sm">+ Jadwalkan Pengiriman</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Referensi Pesanan</th>
                        <th class="px-4 py-3">Alamat Pengiriman</th>
                        <th class="px-4 py-3">Waktu Kirim</th>
                        <th class="px-4 py-3 text-right">Biaya Kirim</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($jadwals as $j)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-900">{{ $j->kode_jadwal }}</td>
                            <td class="px-4 py-3">
                                @if ($j->pesananBorongan)
                                    <a href="{{ route('borongan.show', $j->pesananBorongan) }}" class="font-semibold text-amber-600 hover:underline">{{ $j->pesananBorongan->kode_borongan }}</a>
                                    <p class="text-xs text-slate-500">{{ $j->pesananBorongan->pelanggan->nama_pelanggan }}</p>
                                @elseif ($j->transaksiPenjualan)
                                    <a href="{{ route('penjualan.show', $j->transaksiPenjualan) }}" class="font-semibold text-amber-600 hover:underline">{{ $j->transaksiPenjualan->kode_transaksi }}</a>
                                    <p class="text-xs text-slate-500">{{ $j->transaksiPenjualan->pelanggan?->nama_pelanggan ?? 'Pelanggan Umum' }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <p>{{ $j->alamat_pengiriman }}</p>
                                @if ($j->catatan_bentrok)
                                    <p class="text-xs text-amber-700 mt-0.5">Catatan: {{ $j->catatan_bentrok }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="font-semibold text-slate-900">{{ $j->tanggal_kirim->format('d M Y') }}</p>
                                <p class="text-xs text-slate-500">Pukul {{ $j->jam_kirim }}</p>
                            </td>
                            <td class="px-4 py-3 text-right font-medium">{{ \App\Support\Rupiah::format($j->biaya_pengiriman) }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('pengiriman.status', $j) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status_pengiriman" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-2.5 py-1 text-xs bg-white">
                                        @foreach ($statuses as $st)
                                            <option value="{{ $st->value }}" @selected($j->status_pengiriman === $st)>{{ $st->value }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada jadwal pengiriman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($jadwals->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $jadwals->links() }}</div>
        @endif
    </div>
</x-app-layout>

