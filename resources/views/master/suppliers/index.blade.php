<x-app-layout title="Data Supplier">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode atau nama supplier..." class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-medium cursor-pointer">Cari</button>
        </form>
        <a href="{{ route('suppliers.create') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-4 py-2 text-sm">+ Tambah Supplier</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama Supplier</th>
                        <th class="px-4 py-3">No. Telepon</th>
                        <th class="px-4 py-3">Alamat &amp; Keterangan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($suppliers as $s)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $s->kode_supplier }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $s->nama_supplier }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $s->no_telepon ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                <p>{{ $s->alamat ?? '-' }}</p>
                                @if ($s->keterangan)
                                    <p class="text-xs text-slate-400">{{ $s->keterangan }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('suppliers.edit', $s) }}" class="text-amber-600 hover:text-amber-700 font-medium">Ubah</a>
                                <form
                                    method="POST"
                                    action="{{ route('suppliers.destroy', $s) }}"
                                    class="inline"
                                    data-confirm="Hapus supplier {{ $s->nama_supplier }}?"
                                    data-confirm-title="Hapus Supplier"
                                    data-confirm-button="Ya, Hapus"
                                    data-confirm-variant="danger"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-700 font-medium cursor-pointer">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data supplier.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($suppliers->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $suppliers->links() }}</div>
        @endif
    </div>
</x-app-layout>

