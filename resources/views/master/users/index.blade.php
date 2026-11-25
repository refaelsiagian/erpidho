<x-app-layout title="Pengguna Sistem">
    <div class="flex justify-end">
        <a href="{{ route('pengguna.create') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-4 py-2 text-sm">+ Tambah Pengguna</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama Lengkap</th>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Peran (Role)</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $u)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $u->kode_user }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $u->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $u->username }}</td>
                            <td class="px-4 py-3">
                                <x-badge :color="$u->isPemilik() ? 'amber' : 'sky'">{{ $u->role->value }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('pengguna.edit', $u) }}" class="text-amber-600 hover:text-amber-700 font-medium">Ubah</a>
                                @if (! auth()->user()->is($u))
                                    <form method="POST" action="{{ route('pengguna.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-700 font-medium cursor-pointer">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $users->links() }}</div>
        @endif
    </div>
</x-app-layout>

