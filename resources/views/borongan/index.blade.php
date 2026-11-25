<x-app-layout title="Pesanan Paket Borongan">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('borongan.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode BRN, pelanggan, lokasi..." class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            <select name="status" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-amber-500 outline-none">
                <option value="">Semua Status Proyek</option>
                @foreach ($statuses as $st)
                    <option value="{{ $st->value }}" @selected($selectedStatus === $st->value)>{{ $st->value }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-medium cursor-pointer">Filter</button>
        </form>
        <a href="{{ route('borongan.create') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-4 py-2 text-sm">+ Pesanan Borongan</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Kode BRN</th>
                        <th class="px-4 py-3">Pelanggan &amp; Lokasi</th>
                        <th class="px-4 py-3">Jadwal Proyek</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Total Biaya</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($borongans as $b)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-900">{{ $b->kode_borongan }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $b->pelanggan->nama_pelanggan }}</p>
                                <p class="text-xs text-slate-500">{{ $b->lokasi_proyek }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                {{ $b->tanggal_mulai->format('d M Y') }} – {{ $b->tanggal_target->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                @php($color = match($b->status_proyek) {
                                    \App\Enums\StatusProyek::Selesai => 'emerald',
                                    \App\Enums\StatusProyek::Berjalan => 'sky',
                                    default => 'amber',
                                })
                                <x-badge :color="$color">{{ $b->status_proyek->value }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ \App\Support\Rupiah::format($b->total_biaya) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('borongan.show', $b) }}" class="text-amber-600 hover:text-amber-700 font-medium">Detail</a>
                                <a href="{{ route('borongan.pdf', $b) }}" class="text-slate-600 hover:text-slate-900 font-medium">Nota PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada pesanan borongan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($borongans->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $borongans->links() }}</div>
        @endif
    </div>
</x-app-layout>

