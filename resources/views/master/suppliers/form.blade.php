@php($isEdit = $supplier->exists)
<x-app-layout :title="$isEdit ? 'Ubah Supplier' : 'Tambah Supplier'">
    <div class="max-w-xl bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <form method="POST" action="{{ $isEdit ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="space-y-4">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Supplier</label>
                <input type="text" name="nama_supplier" value="{{ old('nama_supplier', $supplier->nama_supplier) }}" required maxlength="80" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">No. Telepon (Opsional)</label>
                <input type="text" name="no_telepon" value="{{ old('no_telepon', $supplier->no_telepon) }}" maxlength="15" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Alamat (Opsional)</label>
                <textarea name="alamat" rows="2" maxlength="150" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">{{ old('alamat', $supplier->alamat) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" value="{{ old('keterangan', $supplier->keterangan) }}" maxlength="150" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            </div>
            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-5 py-2.5 text-sm cursor-pointer">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Supplier' }}</button>
                <a href="{{ route('suppliers.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>

