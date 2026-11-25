@php
    $materialsJson = $materials->map(fn ($m) => [
        'id' => $m->id,
        'label' => "{$m->kode_barang} - {$m->nama_barang} (Stok: {$m->stok_sisa} {$m->satuan->value})",
        'harga' => $m->harga_jual,
        'stok' => $m->stok_sisa,
    ])->values();
@endphp

<x-app-layout title="Transaksi Penjualan Langsung (POS)">
    <div
        class="max-w-4xl bg-white rounded-2xl border border-slate-200 p-6 shadow-xs"
        x-data="{
            materials: @js($materialsJson),
            modePelanggan: '{{ old('nama_pelanggan_baru') ? 'baru' : 'pilih' }}',
            statusPembayaran: '{{ old('status_pembayaran', 'Lunas') }}',
            perluPengiriman: {{ old('perlu_pengiriman') ? 'true' : 'false' }},
            biayaPengiriman: {{ (int) old('biaya_pengiriman', 0) }},
            items: @js(array_values(old('items', [['material_id' => '', 'jumlah' => 1]]))),
            addItem() { this.items.push({ material_id: '', jumlah: 1 }); },
            removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1); },
            getMaterial(id) { return this.materials.find(m => m.id == id); },
            lineSubtotal(item) {
                const mat = this.getMaterial(item.material_id);
                return mat ? mat.harga * (parseInt(item.jumlah) || 0) : 0;
            },
            materialTotal() {
                return this.items.reduce((sum, item) => sum + this.lineSubtotal(item), 0);
            },
            shippingTotal() {
                return this.perluPengiriman ? (parseInt(this.biayaPengiriman) || 0) : 0;
            },
            grandTotal() {
                return this.materialTotal() + this.shippingTotal();
            },
            rupiah(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
            }
        }"
    >
        <form method="POST" action="{{ route('penjualan.store') }}" class="space-y-6">
            @csrf

            <div class="space-y-3 border-b border-slate-100 pb-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label class="text-sm font-semibold text-slate-800">Pelanggan (Opsional untuk Pembelian Langsung)</label>
                    <div class="flex gap-2 text-xs">
                        <button type="button" @click="modePelanggan = 'pilih'" :class="modePelanggan === 'pilih' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-1.5 rounded-lg font-medium cursor-pointer">Pilih / Umum</button>
                        <button type="button" @click="modePelanggan = 'baru'" :class="modePelanggan === 'baru' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-1.5 rounded-lg font-medium cursor-pointer">+ Pelanggan Baru</button>
                    </div>
                </div>

                <div x-show="modePelanggan === 'pilih'">
                    <select name="pelanggan_id" :disabled="modePelanggan !== 'pilih'" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-amber-500 outline-none">
                        <option value="">Pelanggan Umum (Tanpa Data Pelanggan)</option>
                        @foreach ($pelanggans as $p)
                            <option value="{{ $p->id }}" @selected(old('pelanggan_id') == $p->id)>{{ $p->kode_pelanggan }} — {{ $p->nama_pelanggan }} {{ $p->no_telepon ? "({$p->no_telepon})" : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="modePelanggan === 'baru'" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input type="text" name="nama_pelanggan_baru" :disabled="modePelanggan !== 'baru'" value="{{ old('nama_pelanggan_baru') }}" placeholder="Nama pelanggan baru" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                    <input type="text" name="no_telepon_baru" :disabled="modePelanggan !== 'baru'" value="{{ old('no_telepon_baru') }}" placeholder="No. telepon (opsional)" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                    <input type="text" name="alamat_baru" :disabled="modePelanggan !== 'baru'" value="{{ old('alamat_baru') }}" placeholder="Alamat (opsional)" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800">Daftar Material Dibeli</h2>
                    <button type="button" @click="addItem()" class="text-xs font-semibold bg-slate-900 text-white px-3 py-1.5 rounded-lg hover:bg-slate-800 cursor-pointer">+ Tambah Baris</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <div class="sm:col-span-6">
                                <select :name="`items[${index}][material_id]`" x-model="item.material_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                                    <option value="">-- Pilih Material --</option>
                                    <template x-for="mat in materials" :key="mat.id">
                                        <option :value="mat.id" :selected="mat.id == item.material_id" x-text="mat.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <input type="number" :name="`items[${index}][jumlah]`" x-model="item.jumlah" min="1" :max="getMaterial(item.material_id)?.stok ?? ''" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm" placeholder="Qty">
                            </div>
                            <div class="sm:col-span-3 text-right text-sm font-semibold text-slate-900" x-text="rupiah(lineSubtotal(item))"></div>
                            <div class="sm:col-span-1 text-right">
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-600 hover:text-rose-700 text-xs font-semibold cursor-pointer">Hapus</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                <label class="inline-flex items-center gap-2.5 text-sm font-semibold text-slate-800 cursor-pointer">
                    <input type="checkbox" name="perlu_pengiriman" value="1" x-model="perluPengiriman" class="rounded border-slate-300 text-amber-500">
                    <span>Perlu Pengiriman ke Lokasi Pelanggan?</span>
                </label>
                <div x-show="perluPengiriman" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Biaya Pengiriman (Rp)</label>
                        <input type="number" name="biaya_pengiriman" x-model="biayaPengiriman" min="0" :disabled="!perluPengiriman" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm">
                    </div>
                    <p class="text-xs text-slate-500 self-end pb-2">Biaya langsung masuk total transaksi. Penjadwalan waktu antar dilakukan di menu Jadwal Pengiriman.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Metode Pembayaran</label>
                    <select name="metode_bayar" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                        @foreach ($metodeBayars as $m)
                            <option value="{{ $m->value }}" @selected(old('metode_bayar', 'Tunai') === $m->value)>{{ $m->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status Pembayaran</label>
                    <select name="status_pembayaran" x-model="statusPembayaran" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                        @foreach ($statusPembayarans as $s)
                            <option value="{{ $s->value }}">{{ $s->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="statusPembayaran === 'Lunas'" x-cloak>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Lunas</label>
                    <input type="date" name="tanggal_lunas" value="{{ old('tanggal_lunas', now()->toDateString()) }}" max="{{ now()->toDateString() }}" :disabled="statusPembayaran !== 'Lunas'" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                </div>
            </div>

            <div class="rounded-2xl bg-slate-900 text-white p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs text-slate-400">Material: <span x-text="rupiah(materialTotal())"></span> + Pengiriman: <span x-text="rupiah(shippingTotal())"></span></p>
                    <p class="text-2xl font-bold text-amber-400" x-text="rupiah(grandTotal())"></p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('penjualan.index') }}" class="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-200 hover:bg-slate-800">Batal</a>
                    <button type="submit" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 text-sm cursor-pointer">Simpan Transaksi</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
