@php
    $materialsJson = $materials->map(fn ($m) => [
        'id' => $m->id,
        'label' => "{$m->kode_barang} - {$m->nama_barang} (Sisa: {$m->stok_sisa} {$m->satuan->value})",
        'harga_beli' => $m->harga_beli,
    ])->values();

    $initialItems = $kritisMaterials->isNotEmpty()
        ? $kritisMaterials->map(fn ($m) => [
            'material_id' => $m->id,
            'jumlah_pesan' => max(10, $m->batas_minimum * 2),
            'harga_beli_satuan' => $m->harga_beli,
        ])->values()
        : [['material_id' => '', 'jumlah_pesan' => 10, 'harga_beli_satuan' => 0]];
@endphp

<x-app-layout title="Buat Pesanan Pengadaan">
    <div
        class="max-w-4xl bg-white rounded-2xl border border-slate-200 p-6 shadow-xs"
        x-data="{
            materials: @js($materialsJson),
            items: @js(array_values(old('items') ?: $initialItems)),
            addItem() { this.items.push({ material_id: '', jumlah_pesan: 10, harga_beli_satuan: 0 }); },
            removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1); },
            syncHarga(item) {
                const mat = this.materials.find(m => m.id == item.material_id);
                if (mat) item.harga_beli_satuan = mat.harga_beli;
            },
            lineSubtotal(item) {
                return (parseInt(item.jumlah_pesan) || 0) * (parseInt(item.harga_beli_satuan) || 0);
            },
            grandTotal() {
                return this.items.reduce((sum, item) => sum + this.lineSubtotal(item), 0);
            },
            rupiah(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
            }
        }"
    >
        <form method="POST" action="{{ route('pengadaan.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Supplier</label>
                    <select name="supplier_id" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->kode_supplier }} — {{ $s->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Pesan</label>
                    <input type="date" name="tanggal_pesan" value="{{ old('tanggal_pesan', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800">Daftar Bahan Dipesan</h2>
                    <button type="button" @click="addItem()" class="text-xs font-semibold bg-slate-900 text-white px-3 py-1.5 rounded-lg hover:bg-slate-800 cursor-pointer">+ Tambah Baris</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <div class="sm:col-span-5">
                                <select :name="`items[${index}][material_id]`" x-model="item.material_id" @change="syncHarga(item)" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                                    <option value="">-- Pilih Material --</option>
                                    <template x-for="mat in materials" :key="mat.id">
                                        <option :value="mat.id" :selected="mat.id == item.material_id" x-text="mat.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <input type="number" :name="`items[${index}][jumlah_pesan]`" x-model="item.jumlah_pesan" min="1" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm" placeholder="Qty">
                            </div>
                            <div class="sm:col-span-2">
                                <input type="number" :name="`items[${index}][harga_beli_satuan]`" x-model="item.harga_beli_satuan" min="0" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm" placeholder="Harga beli">
                            </div>
                            <div class="sm:col-span-2 text-right text-sm font-semibold text-slate-900" x-text="rupiah(lineSubtotal(item))"></div>
                            <div class="sm:col-span-1 text-right">
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-600 hover:text-rose-700 text-xs font-semibold cursor-pointer">Hapus</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="rounded-2xl bg-slate-900 text-white p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Total Pengadaan</p>
                    <p class="text-2xl font-bold text-amber-400" x-text="rupiah(grandTotal())"></p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('pengadaan.index') }}" class="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-200 hover:bg-slate-800">Batal</a>
                    <button type="submit" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 text-sm cursor-pointer">Simpan Pesanan</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>

