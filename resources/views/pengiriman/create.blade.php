@php
    $penjualanList = $transaksis->map(fn ($t) => [
        'id' => $t->id,
        'label' => "{$t->kode_transaksi} — " . ($t->pelanggan?->nama_pelanggan ?? 'Pelanggan Umum'),
        'alamat' => $t->pelanggan?->alamat ?? '',
        'biaya' => $t->biaya_pengiriman,
    ])->values();

    $boronganList = $borongans->map(fn ($b) => [
        'id' => $b->id,
        'label' => "{$b->kode_borongan} — {$b->pelanggan->nama_pelanggan}",
        'alamat' => $b->lokasi_proyek,
        'biaya' => $b->biaya_pengiriman,
    ])->values();
@endphp

<x-app-layout title="Buat Jadwal Pengiriman">
    <div
        class="max-w-2xl bg-white rounded-2xl border border-slate-200 p-6 shadow-xs"
        x-data="{
            jenis: '{{ old('jenis_referensi', $preselectType ?: 'borongan') }}',
            referensiId: '{{ old('referensi_id', $preselectId ?: '') }}',
            alamat: '{{ old('alamat_pengiriman', '') }}',
            tanggalKirim: '{{ old('tanggal_kirim', now()->addDay()->toDateString()) }}',
            penjualans: @js($penjualanList),
            borongans: @js($boronganList),
            get options() { return this.jenis === 'penjualan' ? this.penjualans : this.borongans; },
            get selectedOrder() { return this.options.find(o => o.id == this.referensiId); },
            syncAlamat() {
                const found = this.selectedOrder;
                if (found && found.alamat && !this.alamat) {
                    this.alamat = found.alamat;
                }
            },
            rupiah(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
            }
        }"
        x-init="syncAlamat()"
    >
        @if (session('buffer_warning'))
            <div class="mb-5 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 space-y-2">
                <p class="font-bold">Peringatan Jadwal Berdekatan (&plusmn;1 Jam)</p>
                <p>{{ session('buffer_warning') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('pengiriman.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Pesanan</label>
                    <select name="jenis_referensi" x-model="jenis" @change="referensiId = ''; alamat = ''" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                        <option value="borongan">Pesanan Borongan (BRN)</option>
                        <option value="penjualan">Penjualan Langsung (TRX)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pilih Transaksi / Proyek</label>
                    <select name="referensi_id" x-model="referensiId" @change="alamat = ''; syncAlamat()" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                        <option value="">-- Pilih Referensi --</option>
                        <template x-for="opt in options" :key="opt.id">
                            <option :value="opt.id" :selected="opt.id == referensiId" x-text="opt.label"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div x-show="selectedOrder" x-cloak class="rounded-xl bg-slate-50 border border-slate-200 px-3.5 py-2.5 text-xs text-slate-600 flex items-center justify-between">
                <span>Biaya pengiriman tercatat pada pesanan (sudah masuk total nota):</span>
                <span class="font-bold text-slate-900" x-text="rupiah(selectedOrder?.biaya)"></span>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Pengiriman</label>
                <input type="text" name="alamat_pengiriman" x-model="alamat" required maxlength="150" placeholder="Alamat tujuan pengiriman" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Kirim</label>
                    <input type="date" name="tanggal_kirim" x-model="tanggalKirim" min="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jam Kirim</label>
                    <input type="time" name="jam_kirim" value="{{ old('jam_kirim', '09:00') }}" :min="tanggalKirim === '{{ now()->toDateString() }}' ? '{{ now()->format('H:i') }}' : null" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm">
                </div>
            </div>

            @if (session('buffer_warning'))
                <label class="flex items-start gap-2.5 rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 cursor-pointer">
                    <input type="checkbox" name="confirm_buffer_warning" value="1" class="mt-0.5 rounded text-amber-600">
                    <span>Saya memahami jadwal pengiriman ini berdekatan (&le; 1 jam) dengan jadwal lain dan tetap ingin menyimpannya.</span>
                </label>
            @endif

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-5 py-2.5 text-sm cursor-pointer">Simpan Jadwal</button>
                <a href="{{ route('pengiriman.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
