@php($isEdit = $pelanggan->exists)
<x-app-layout :title="$isEdit ? 'Ubah Pelanggan' : 'Tambah Pelanggan'">
    <div class="max-w-xl bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <form method="POST" action="{{ $isEdit ? route('pelanggans.update', $pelanggan) : route('pelanggans.store') }}" class="space-y-4">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Pelanggan</label>
                <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan', $pelanggan->nama_pelanggan) }}" required maxlength="60" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">No. Telepon (Opsional)</label>
                <input type="text" name="no_telepon" value="{{ old('no_telepon', $pelanggan->no_telepon) }}" maxlength="15" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Alamat (Opsional)</label>
                <textarea name="alamat" rows="3" maxlength="150" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">{{ old('alamat', $pelanggan->alamat) }}</textarea>
            </div>
            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-5 py-2.5 text-sm cursor-pointer">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pelanggan' }}</button>
                <a href="{{ route('pelanggans.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>

