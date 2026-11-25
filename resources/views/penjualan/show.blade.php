<x-app-layout :title="'Detail Transaksi ' . $transaksi->kode_transaksi">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('penjualan.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali ke Daftar Penjualan</a>
        <div class="flex flex-wrap items-center gap-2">
            @if (! $transaksi->jadwalPengiriman)
                <a href="{{ route('pengiriman.create', ['jenis' => 'penjualan', 'id' => $transaksi->id]) }}" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-semibold">+ Jadwalkan Pengiriman</a>
            @endif
            <a href="{{ route('penjualan.pdf', $transaksi) }}" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 px-4 py-2 text-sm font-semibold">Unduh Nota PDF</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <p class="text-xs text-slate-500 uppercase">Kode Transaksi</p>
                    <h2 class="text-xl font-bold text-slate-900">{{ $transaksi->kode_transaksi }}</h2>
                    <p class="text-xs text-slate-500 mt-1">{{ $transaksi->tanggal->format('d M Y, H:i') }} · Kasir: {{ $transaksi->user->nama_lengkap }}</p>
                </div>
                <x-badge :color="$transaksi->status_pembayaran === \App\Enums\StatusPembayaran::Lunas ? 'emerald' : 'amber'">
                    {{ $transaksi->status_pembayaran->value }} ({{ $transaksi->metode_bayar->value }})
                </x-badge>
            </div>

            <div class="text-sm">
                <p class="text-xs text-slate-500 uppercase">Pelanggan</p>
                <p class="font-semibold text-slate-900">{{ $transaksi->pelanggan?->nama_pelanggan ?? 'Pelanggan Umum' }}</p>
                @if ($transaksi->pelanggan)
                    <p class="text-slate-600 text-xs">{{ $transaksi->pelanggan->no_telepon }} · {{ $transaksi->pelanggan->alamat }}</p>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                        <tr>
                            <th class="px-3 py-2.5">Material</th>
                            <th class="px-3 py-2.5 text-center">Qty</th>
                            <th class="px-3 py-2.5 text-right">Harga Satuan</th>
                            <th class="px-3 py-2.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transaksi->details as $d)
                            <tr>
                                <td class="px-3 py-2.5 font-medium text-slate-900">{{ $d->material->nama_barang }}</td>
                                <td class="px-3 py-2.5 text-center">{{ $d->jumlah }} {{ $d->material->satuan->value }}</td>
                                <td class="px-3 py-2.5 text-right">{{ \App\Support\Rupiah::format($d->harga_satuan) }}</td>
                                <td class="px-3 py-2.5 text-right font-semibold">{{ \App\Support\Rupiah::format($d->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-slate-200 text-sm">
                        @if ($transaksi->biaya_pengiriman > 0)
                            <tr>
                                <td colspan="3" class="px-3 py-2 text-right text-slate-600">Biaya Pengiriman</td>
                                <td class="px-3 py-2 text-right font-medium">{{ \App\Support\Rupiah::format($transaksi->biaya_pengiriman) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="px-3 py-3 text-right font-bold text-slate-900">Total Bayar</td>
                            <td class="px-3 py-3 text-right font-bold text-lg text-amber-600">{{ \App\Support\Rupiah::format($transaksi->total_bayar) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
                <h3 class="font-bold text-slate-900 text-sm">Perbarui Status Pembayaran</h3>
                <form method="POST" action="{{ route('penjualan.status', $transaksi) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <select name="status_pembayaran" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        @foreach (\App\Enums\StatusPembayaran::cases() as $st)
                            <option value="{{ $st->value }}" @selected($transaksi->status_pembayaran === $st)>{{ $st->value }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full rounded-xl bg-slate-900 hover:bg-slate-800 text-white py-2 text-sm font-semibold cursor-pointer">Simpan Status</button>
                </form>
            </div>

            @if ($transaksi->jadwalPengiriman)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2 text-sm">
                    <h3 class="font-bold text-slate-900">Info Pengiriman</h3>
                    <p class="font-mono text-xs text-slate-500">{{ $transaksi->jadwalPengiriman->kode_jadwal }}</p>
                    <p class="text-slate-700">{{ $transaksi->jadwalPengiriman->alamat_pengiriman }}</p>
                    <p class="text-xs text-slate-500">{{ $transaksi->jadwalPengiriman->tanggal_kirim->format('d M Y') }} · {{ $transaksi->jadwalPengiriman->jam_kirim }}</p>
                    <x-badge color="sky">{{ $transaksi->jadwalPengiriman->status_pengiriman->value }}</x-badge>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

