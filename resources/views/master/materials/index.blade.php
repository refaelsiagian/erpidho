<x-app-layout title="Data Material">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('materials.index') }}" class="flex flex-wrap items-center gap-2">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari kode / nama barang..."
                class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm focus:border-amber-500 outline-none"
            >
            <select name="kategori" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-amber-500 outline-none">
                <option value="">Semua Kategori</option>
                @foreach ($kategoris as $kat)
                    <option value="{{ $kat->value }}" @selected($selectedKategori === $kat->value)>{{ $kat->value }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700 bg-white border border-slate-300 rounded-xl px-3 py-2 cursor-pointer">
                <input type="checkbox" name="kritis" value="1" @checked($hanyaKritis) class="rounded text-amber-500">
                <span>Stok Kritis</span>
            </label>
            <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-medium cursor-pointer">
                Filter
            </button>
        </form>

        @if (auth()->user()->isPemilik())
            <a href="{{ route('materials.create') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-4 py-2 text-sm transition">
                + Tambah Material
            </a>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama Material</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-right">Harga Beli</th>
                        <th class="px-4 py-3 text-right">Harga Jual</th>
                        <th class="px-4 py-3 text-center">Stok</th>
                        @if (auth()->user()->isPemilik())
                            <th class="px-4 py-3 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($materials as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $item->kode_barang }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $item->nama_barang }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $item->kategori->value }} ({{ $item->satuan->value }})</td>
                            <td class="px-4 py-3 text-right text-slate-600">{{ \App\Support\Rupiah::format($item->harga_beli) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ \App\Support\Rupiah::format($item->harga_jual) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($item->isStokKritis())
                                    <x-badge color="rose">{{ $item->stok_sisa }} (Min {{ $item->batas_minimum }})</x-badge>
                                @else
                                    <x-badge color="emerald">{{ $item->stok_sisa }} (Min {{ $item->batas_minimum }})</x-badge>
                                @endif
                            </td>
                            @if (auth()->user()->isPemilik())
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                    <a href="{{ route('materials.edit', $item) }}" class="text-amber-600 hover:text-amber-700 font-medium">Ubah</a>
                                    <form method="POST" action="{{ route('materials.destroy', $item) }}" class="inline" onsubmit="return confirm('Hapus material ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-700 font-medium cursor-pointer">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">Data material tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($materials->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $materials->links() }}</div>
        @endif
    </div>
</x-app-layout>

