@php($isEdit = $material->exists)
<x-app-layout :title="$isEdit ? 'Ubah Material' : 'Tambah Material'">
    <div class="max-w-2xl bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <form method="POST" action="{{ $isEdit ? route('materials.update', $material) : route('materials.store') }}" class="space-y-4">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Barang</label>
                <input type="text" name="nama_barang" value="{{ old('nama_barang', $material->nama_barang) }}" required maxlength="100" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                    <select name="kategori" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
                        @foreach ($kategoris as $kat)
                            <option value="{{ $kat->value }}" @selected(old('kategori', $material->kategori?->value) === $kat->value)>{{ $kat->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Satuan</label>
                    <select name="satuan" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
                        @foreach ($satuans as $sat)
                            <option value="{{ $sat->value }}" @selected(old('satuan', $material->satuan?->value) === $sat->value)>{{ $sat->value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Harga Beli (Rp)</label>
                    <input type="number" name="harga_beli" min="0" value="{{ old('harga_beli', $material->harga_beli ?? 0) }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Harga Jual (Rp)</label>
                    <input type="number" name="harga_jual" min="0" value="{{ old('harga_jual', $material->harga_jual ?? 0) }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Stok Sisa</label>
                    <input type="number" name="stok_sisa" min="0" value="{{ old('stok_sisa', $material->stok_sisa ?? 0) }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Batas Minimum (Reorder Point)</label>
                    <input type="number" name="batas_minimum" min="1" value="{{ old('batas_minimum', $material->batas_minimum ?? 5) }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-5 py-2.5 text-sm cursor-pointer">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Material' }}
                </button>
                <a href="{{ route('materials.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>

