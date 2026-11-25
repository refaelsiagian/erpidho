<x-app-layout title="Data Karyawan Tukang">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('tukangs.index') }}" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode atau nama tukang..." class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-medium cursor-pointer">Cari</button>
        </form>
        <a href="{{ route('tukangs.create') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-4 py-2 text-sm">+ Tambah Tukang</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama Tukang</th>
                        <th class="px-4 py-3">No. Telepon</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tukangs as $t)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $t->kode_tukang }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $t->nama_tukang }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $t->no_telepon }}</td>
                            <td class="px-4 py-3">
                                <x-badge :color="$t->status_karyawan === \App\Enums\StatusKaryawan::Aktif ? 'emerald' : 'slate'">{{ $t->status_karyawan->value }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('tukangs.edit', $t) }}" class="text-amber-600 hover:text-amber-700 font-medium">Ubah</a>
                                <form
                                    method="POST"
                                    action="{{ route('tukangs.destroy', $t) }}"
                                    class="inline"
                                    data-confirm="Hapus karyawan tukang {{ $t->nama_tukang }}?"
                                    data-confirm-title="Hapus Karyawan Tukang"
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
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data karyawan tukang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tukangs->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $tukangs->links() }}</div>
        @endif
    </div>
</x-app-layout>

