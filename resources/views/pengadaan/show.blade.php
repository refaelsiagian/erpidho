<x-app-layout :title="'Detail Pengadaan ' . $pengadaan->kode_pengadaan">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('pengadaan.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali ke Daftar Pengadaan</a>
        <div class="flex items-center gap-2">
            @if ($pengadaan->status_penerimaan === \App\Enums\StatusPenerimaan::Dipesan)
                <form
                    method="POST"
                    action="{{ route('pengadaan.receive', $pengadaan) }}"
                    data-confirm="Konfirmasi seluruh material telah diterima dan tambahkan ke stok?"
                    data-confirm-title="Konfirmasi Penerimaan Barang"
                    data-confirm-button="Ya, Terima Barang"
                    data-confirm-variant="success"
                >
                    @csrf
                    <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-sm font-semibold cursor-pointer">
                        Konfirmasi Barang Diterima (+ Stok)
                    </button>
                </form>
            @endif
            <a href="{{ route('pengadaan.pdf', $pengadaan) }}" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 px-4 py-2 text-sm font-semibold">Unduh Daftar Pesanan PDF</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <p class="text-xs text-slate-500 uppercase">Kode Pengadaan</p>
                <h2 class="text-xl font-bold text-slate-900">{{ $pengadaan->kode_pengadaan }}</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Supplier: <span class="font-semibold text-slate-700">{{ $pengadaan->supplier->nama_supplier }}</span> ·
                    Tanggal Pesan: {{ $pengadaan->tanggal_pesan->format('d M Y') }}
                    @if ($pengadaan->tanggal_terima)
                        · Diterima: {{ $pengadaan->tanggal_terima->format('d M Y') }}
                    @endif
                </p>
            </div>
            <x-badge :color="$pengadaan->status_penerimaan === \App\Enums\StatusPenerimaan::Diterima ? 'emerald' : 'amber'">
                {{ $pengadaan->status_penerimaan->value }}
            </x-badge>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                    <tr>
                        <th class="px-3 py-2.5">Material</th>
                        <th class="px-3 py-2.5 text-center">Jumlah Pesan</th>
                        <th class="px-3 py-2.5 text-right">Harga Beli Satuan</th>
                        <th class="px-3 py-2.5 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($pengadaan->details as $d)
                        <tr>
                            <td class="px-3 py-2.5 font-medium text-slate-900">{{ $d->material->nama_barang }}</td>
                            <td class="px-3 py-2.5 text-center">{{ $d->jumlah_pesan }} {{ $d->material->satuan->value }}</td>
                            <td class="px-3 py-2.5 text-right">{{ \App\Support\Rupiah::format($d->harga_beli_satuan) }}</td>
                            <td class="px-3 py-2.5 text-right font-semibold">{{ \App\Support\Rupiah::format($d->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-slate-200">
                    <tr>
                        <td colspan="3" class="px-3 py-3 text-right font-bold text-slate-900">Total Pengadaan</td>
                        <td class="px-3 py-3 text-right font-bold text-lg text-amber-600">{{ \App\Support\Rupiah::format($pengadaan->total_pengadaan) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>

