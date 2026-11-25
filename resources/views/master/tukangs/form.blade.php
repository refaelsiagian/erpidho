@php($isEdit = $tukang->exists)
<x-app-layout :title="$isEdit ? 'Ubah Karyawan Tukang' : 'Tambah Karyawan Tukang'">
    <div class="max-w-xl bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <form method="POST" action="{{ $isEdit ? route('tukangs.update', $tukang) : route('tukangs.store') }}" class="space-y-4">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Tukang</label>
                <input type="text" name="nama_tukang" value="{{ old('nama_tukang', $tukang->nama_tukang) }}" required maxlength="60" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">No. Telepon</label>
                <input type="text" name="no_telepon" value="{{ old('no_telepon', $tukang->no_telepon) }}" required maxlength="15" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status Karyawan</label>
                <select name="status_karyawan" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
                    @foreach ($statuses as $st)
                        <option value="{{ $st->value }}" @selected(old('status_karyawan', $tukang->status_karyawan?->value) === $st->value)>{{ $st->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-5 py-2.5 text-sm cursor-pointer">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Tukang' }}</button>
                <a href="{{ route('tukangs.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>

