<x-app-layout title="Penjualan Langsung (POS)">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('penjualan.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode TRX / pelanggan..." class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            <select name="status" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-amber-500 outline-none">
                <option value="">Semua Status</option>
                @foreach ($statuses as $st)
                    <option value="{{ $st->value }}" @selected($selectedStatus === $st->value)>{{ $st->value }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-medium cursor-pointer">Filter</button>
        </form>
        <a href="{{ route('penjualan.create') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-4 py-2 text-sm">+ Transaksi Baru</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Kode TRX</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Metode</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Total Bayar</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transaksis as $trx)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-900">{{ $trx->kode_transaksi }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $trx->tanggal->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $trx->pelanggan?->nama_pelanggan ?? 'Pelanggan Umum' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $trx->metode_bayar->value }}</td>
                            <td class="px-4 py-3">
                                <x-badge :color="$trx->status_pembayaran === \App\Enums\StatusPembayaran::Lunas ? 'emerald' : 'amber'">{{ $trx->status_pembayaran->value }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ \App\Support\Rupiah::format($trx->total_bayar) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('penjualan.show', $trx) }}" class="text-amber-600 hover:text-amber-700 font-medium">Detail</a>
                                <a href="{{ route('penjualan.pdf', $trx) }}" class="text-slate-600 hover:text-slate-900 font-medium">Nota PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada transaksi penjualan langsung.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transaksis->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $transaksis->links() }}</div>
        @endif
    </div>
</x-app-layout>

