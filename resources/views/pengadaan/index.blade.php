<x-app-layout title="Pengadaan Bahan &amp; Pemantauan Stok">
    @if ($stokKritis->isNotEmpty())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-bold text-rose-900">Peringatan Stok Minimum ({{ $stokKritis->count() }} Material)</h2>
                    <p class="text-xs text-rose-700">Material berikut sudah menyentuh atau di bawah batas minimum stok dan perlu dipesan ulang ke supplier.</p>
                </div>
                <a href="{{ route('pengadaan.create', ['kritis' => 1]) }}" class="inline-flex items-center justify-center rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold px-4 py-2 text-xs shrink-0">
                    + Buat Pengadaan Stok Kritis
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                @foreach ($stokKritis as $item)
                    <div class="bg-white rounded-xl border border-rose-200 px-3 py-2 text-xs">
                        <p class="font-semibold text-slate-900 truncate">{{ $item->nama_barang }}</p>
                        <p class="text-rose-600 font-medium">Sisa: {{ $item->stok_sisa }} / Min: {{ $item->batas_minimum }} {{ $item->satuan->value }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('pengadaan.index') }}" class="flex items-center gap-2">
            <select name="status" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach ($statuses as $st)
                    <option value="{{ $st->value }}" @selected($selectedStatus === $st->value)>{{ $st->value }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-medium cursor-pointer">Filter</button>
        </form>
        <a href="{{ route('pengadaan.create') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-4 py-2 text-sm">+ Buat Pengadaan Baru</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Kode PGD</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Tanggal Pesan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Total Pengadaan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pengadaans as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-900">{{ $p->kode_pengadaan }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $p->supplier->nama_supplier }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $p->tanggal_pesan->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <x-badge :color="$p->status_penerimaan === \App\Enums\StatusPenerimaan::Diterima ? 'emerald' : 'amber'">{{ $p->status_penerimaan->value }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">{{ \App\Support\Rupiah::format($p->total_pengadaan) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('pengadaan.show', $p) }}" class="text-amber-600 hover:text-amber-700 font-medium">Detail</a>
                                <a href="{{ route('pengadaan.pdf', $p) }}" class="text-slate-600 hover:text-slate-900 font-medium">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat pengadaan bahan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pengadaans->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $pengadaans->links() }}</div>
        @endif
    </div>
</x-app-layout>

