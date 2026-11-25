<x-app-layout :title="'Detail Transaksi ' . $transaksi->kode_transaksi">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('penjualan.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali ke Daftar Penjualan</a>
        <div class="flex flex-wrap items-center gap-2">
            @if ($transaksi->perlu_pengiriman && ! $transaksi->jadwalPengiriman)
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
                <h3 class="font-bold text-slate-900 text-sm">Status Pembayaran</h3>
                @if ($transaksi->status_pembayaran === \App\Enums\StatusPembayaran::BelumLunas)
                    <form
                        method="POST"
                        action="{{ route('penjualan.status', $transaksi) }}"
                        class="space-y-2.5"
                        data-confirm="Tandai transaksi {{ $transaksi->kode_transaksi }} sebagai Lunas?"
                        data-confirm-title="Konfirmasi Pelunasan"
                        data-confirm-button="Ya, Tandai Lunas"
                        data-confirm-variant="success"
                    >
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status_pembayaran" value="{{ \App\Enums\StatusPembayaran::Lunas->value }}">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Tanggal Lunas</label>
                            <input type="date" name="tanggal_lunas" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white py-2 text-sm font-semibold cursor-pointer">
                            Tandai Lunas
                        </button>
                    </form>
                @elseif (auth()->user()->isPemilik())
                    <x-badge color="emerald">Lunas</x-badge>
                    @if ($transaksi->tanggal_lunas)
                        <p class="text-xs text-slate-500">Tanggal Lunas: {{ $transaksi->tanggal_lunas->format('d M Y') }}</p>
                    @endif
                    <form
                        method="POST"
                        action="{{ route('penjualan.status', $transaksi) }}"
                        class="pt-1"
                        data-confirm="Kembalikan status transaksi {{ $transaksi->kode_transaksi }} menjadi Belum Lunas? Tindakan ini khusus Pemilik."
                        data-confirm-title="Kembalikan Status Pembayaran"
                        data-confirm-button="Ya, Kembalikan"
                        data-confirm-variant="warning"
                    >
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status_pembayaran" value="{{ \App\Enums\StatusPembayaran::BelumLunas->value }}">
                        <button type="submit" class="w-full rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-900 py-2 text-xs font-semibold cursor-pointer">
                            Kembalikan ke Belum Lunas
                        </button>
                    </form>
                @else
                    <x-badge color="emerald">Lunas (Terkunci)</x-badge>
                    @if ($transaksi->tanggal_lunas)
                        <p class="text-xs text-slate-500">Tanggal Lunas: {{ $transaksi->tanggal_lunas->format('d M Y') }}</p>
                    @endif
                    <p class="text-xs text-slate-500">Hanya Pemilik yang dapat mengubah kembali status Lunas.</p>
                @endif
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

