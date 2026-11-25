@php
    $materialsJson = $materials->map(fn ($m) => [
        'id' => $m->id,
        'label' => "{$m->kode_barang} - {$m->nama_barang} (Stok: {$m->stok_sisa} {$m->satuan->value})",
        'harga' => $m->harga_jual,
        'stok' => $m->stok_sisa,
    ])->values();
@endphp

<x-app-layout title="Buat Pesanan Borongan">
    <div
        class="max-w-4xl bg-white rounded-2xl border border-slate-200 p-6 shadow-xs"
        x-data="{
            materials: @js($materialsJson),
            modePelanggan: '{{ old('nama_pelanggan_baru') ? 'baru' : 'pilih' }}',
            biayaJasa: {{ (int) old('biaya_jasa', 0) }},
            items: [{ material_id: '', jumlah_material: 1 }],
            addItem() { this.items.push({ material_id: '', jumlah_material: 1 }); },
            removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1); },
            getMaterial(id) { return this.materials.find(m => m.id == id); },
            lineSubtotal(item) {
                const mat = this.getMaterial(item.material_id);
                return mat ? mat.harga * (parseInt(item.jumlah_material) || 0) : 0;
            },
            materialTotal() {
                return this.items.reduce((sum, item) => sum + this.lineSubtotal(item), 0);
            },
            grandTotal() {
                return this.materialTotal() + (parseInt(this.biayaJasa) || 0);
            },
            rupiah(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
            }
        }"
    >
        <form method="POST" action="{{ route('borongan.store') }}" class="space-y-6">
            @csrf

            <div class="space-y-3 border-b border-slate-100 pb-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label class="text-sm font-semibold text-slate-800">Data Pelanggan (Wajib untuk Borongan)</label>
                    <div class="flex gap-2 text-xs">
                        <button type="button" @click="modePelanggan = 'pilih'" :class="modePelanggan === 'pilih' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-1.5 rounded-lg font-medium cursor-pointer">Pilih Pelanggan</button>
                        <button type="button" @click="modePelanggan = 'baru'" :class="modePelanggan === 'baru' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-1.5 rounded-lg font-medium cursor-pointer">+ Pelanggan Baru</button>
                    </div>
                </div>

                <div x-show="modePelanggan === 'pilih'">
                    <select name="pelanggan_id" :disabled="modePelanggan !== 'pilih'" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach ($pelanggans as $p)
                            <option value="{{ $p->id }}" @selected(old('pelanggan_id') == $p->id)>{{ $p->kode_pelanggan }} — {{ $p->nama_pelanggan }} {{ $p->no_telepon ? "({$p->no_telepon})" : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="modePelanggan === 'baru'" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input type="text" name="nama_pelanggan_baru" :disabled="modePelanggan !== 'baru'" value="{{ old('nama_pelanggan_baru') }}" placeholder="Nama pelanggan baru" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                    <input type="text" name="no_telepon_baru" :disabled="modePelanggan !== 'baru'" value="{{ old('no_telepon_baru') }}" placeholder="No. telepon" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                    <input type="text" name="alamat_baru" :disabled="modePelanggan !== 'baru'" value="{{ old('alamat_baru') }}" placeholder="Alamat" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', now()->toDateString()) }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Target Selesai</label>
                    <input type="date" name="tanggal_target" value="{{ old('tanggal_target', now()->addDays(5)->toDateString()) }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Lokasi Proyek</label>
                    <input type="text" name="lokasi_proyek" value="{{ old('lokasi_proyek') }}" required maxlength="150" placeholder="Alamat lengkap lokasi pemasangan" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Biaya Jasa Pemasangan (Rp - Diisi Manual)</label>
                    <input type="number" name="biaya_jasa" x-model="biayaJasa" min="0" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800">Alokasi Material Proyek</h2>
                    <button type="button" @click="addItem()" class="text-xs font-semibold bg-slate-900 text-white px-3 py-1.5 rounded-lg hover:bg-slate-800 cursor-pointer">+ Tambah Material</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <div class="sm:col-span-6">
                                <select :name="`items[${index}][material_id]`" x-model="item.material_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                                    <option value="">-- Pilih Material --</option>
                                    <template x-for="mat in materials" :key="mat.id">
                                        <option :value="mat.id" x-text="mat.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <input type="number" :name="`items[${index}][jumlah_material]`" x-model="item.jumlah_material" min="1" :max="getMaterial(item.material_id)?.stok ?? ''" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm" placeholder="Qty">
                            </div>
                            <div class="sm:col-span-3 text-right text-sm font-semibold text-slate-900" x-text="rupiah(lineSubtotal(item))"></div>
                            <div class="sm:col-span-1 text-right">
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-600 hover:text-rose-700 text-xs font-semibold cursor-pointer">Hapus</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="rounded-2xl bg-slate-900 text-white p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs text-slate-400">Material: <span x-text="rupiah(materialTotal())"></span> + Jasa: <span x-text="rupiah(biayaJasa)"></span></p>
                    <p class="text-2xl font-bold text-amber-400" x-text="rupiah(grandTotal())"></p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('borongan.index') }}" class="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-200 hover:bg-slate-800">Batal</a>
                    <button type="submit" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 text-sm cursor-pointer">Simpan Pesanan Borongan</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>

