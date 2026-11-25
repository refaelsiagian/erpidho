<x-app-layout :title="'Detail Borongan ' . $borongan->kode_borongan">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('borongan.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali ke Daftar Borongan</a>
        <div class="flex flex-wrap items-center gap-2">
            @if (! $borongan->jadwalPengiriman)
                <a href="{{ route('pengiriman.create', ['jenis' => 'borongan', 'id' => $borongan->id]) }}" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-semibold">+ Jadwalkan Pengiriman</a>
            @endif
            <a href="{{ route('borongan.pdf', $borongan) }}" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 px-4 py-2 text-sm font-semibold">Unduh Nota PDF</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <p class="text-xs text-slate-500 uppercase">Kode Borongan</p>
                    <h2 class="text-xl font-bold text-slate-900">{{ $borongan->kode_borongan }}</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Periode: {{ $borongan->tanggal_mulai->format('d M Y') }} – {{ $borongan->tanggal_target->format('d M Y') }} · Dicatat: {{ $borongan->user->nama_lengkap }}
                    </p>
                </div>
                @php($color = match($borongan->status_proyek) {
                    \App\Enums\StatusProyek::Selesai => 'emerald',
                    \App\Enums\StatusProyek::Berjalan => 'sky',
                    default => 'amber',
                })
                <div class="flex flex-wrap gap-1.5">
                    <x-badge :color="$color">{{ $borongan->status_proyek->value }}</x-badge>
                    <x-badge :color="$borongan->isLunas() ? 'emerald' : 'amber'">{{ $borongan->status_pembayaran->value }} ({{ $borongan->metode_bayar->value }})</x-badge>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-slate-500 uppercase">Pelanggan</p>
                    <p class="font-semibold text-slate-900">{{ $borongan->pelanggan->nama_pelanggan }}</p>
                    <p class="text-xs text-slate-600">{{ $borongan->pelanggan->no_telepon }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase">Lokasi Proyek</p>
                    <p class="font-medium text-slate-800">{{ $borongan->lokasi_proyek }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                        <tr>
                            <th class="px-3 py-2.5">Material</th>
                            <th class="px-3 py-2.5 text-center">Jumlah</th>
                            <th class="px-3 py-2.5 text-right">Harga Satuan</th>
                            <th class="px-3 py-2.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($borongan->details as $d)
                            <tr>
                                <td class="px-3 py-2.5 font-medium text-slate-900">{{ $d->material->nama_barang }}</td>
                                <td class="px-3 py-2.5 text-center">{{ $d->jumlah_material }} {{ $d->material->satuan->value }}</td>
                                <td class="px-3 py-2.5 text-right">{{ \App\Support\Rupiah::format($d->harga_satuan) }}</td>
                                <td class="px-3 py-2.5 text-right font-semibold">{{ \App\Support\Rupiah::format($d->subtotal_material) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-slate-200 text-sm">
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-right text-slate-600">Biaya Jasa Pemasangan</td>
                            <td class="px-3 py-2 text-right font-medium">{{ \App\Support\Rupiah::format($borongan->biaya_jasa) }}</td>
                        </tr>
                        @if ($borongan->biaya_pengiriman > 0)
                            <tr>
                                <td colspan="3" class="px-3 py-2 text-right text-slate-600">Biaya Pengiriman</td>
                                <td class="px-3 py-2 text-right font-medium">{{ \App\Support\Rupiah::format($borongan->biaya_pengiriman) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="px-3 py-3 text-right font-bold text-slate-900">Total Biaya Borongan</td>
                            <td class="px-3 py-3 text-right font-bold text-lg text-amber-600">{{ \App\Support\Rupiah::format($borongan->total_biaya) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
                <h3 class="font-bold text-slate-900 text-sm">Status Pembayaran</h3>
                @if (! $borongan->isLunas())
                    <form
                        method="POST"
                        action="{{ route('borongan.payment', $borongan) }}"
                        class="space-y-2.5"
                        data-confirm="Tandai borongan {{ $borongan->kode_borongan }} sebagai Lunas? Nilai proyek akan masuk pemasukan keuangan."
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
                    <x-badge color="emerald">Lunas ({{ $borongan->metode_bayar->value }})</x-badge>
                    @if ($borongan->tanggal_lunas)
                        <p class="text-xs text-slate-500">Tanggal Lunas: {{ $borongan->tanggal_lunas->format('d M Y') }}</p>
                    @endif
                    <form
                        method="POST"
                        action="{{ route('borongan.payment', $borongan) }}"
                        class="pt-1"
                        data-confirm="Kembalikan status pembayaran {{ $borongan->kode_borongan }} menjadi Belum Lunas? Tindakan ini khusus Pemilik."
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
                    @if ($borongan->tanggal_lunas)
                        <p class="text-xs text-slate-500">Tanggal Lunas: {{ $borongan->tanggal_lunas->format('d M Y') }}</p>
                    @endif
                    <p class="text-xs text-slate-500">Hanya Pemilik yang dapat mengubah kembali status Lunas.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
                <h3 class="font-bold text-slate-900 text-sm">Status Proyek</h3>
                @if ($borongan->status_proyek === \App\Enums\StatusProyek::Selesai)
                    <x-badge color="emerald">Selesai (Terkunci)</x-badge>
                    <p class="text-xs text-slate-500">Proyek yang sudah diverifikasi Selesai tidak dapat dikembalikan ke status sebelumnya.</p>
                @elseif ($borongan->status_proyek === \App\Enums\StatusProyek::Berjalan && ! auth()->user()->isPemilik())
                    <x-badge color="sky">Sedang Berjalan</x-badge>
                    <p class="text-xs text-slate-500">Menunggu verifikasi Selesai oleh Pemilik (atau melalui menu Penggajian Tukang).</p>
                @else
                    @php($defaultNextStatus = $borongan->status_proyek === \App\Enums\StatusProyek::Menunggu
                        ? \App\Enums\StatusProyek::Berjalan->value
                        : \App\Enums\StatusProyek::Selesai->value)
                    <form
                        method="POST"
                        action="{{ route('borongan.status', $borongan) }}"
                        class="space-y-3"
                        x-data="{ targetStatus: '{{ $defaultNextStatus }}' }"
                        :data-confirm="targetStatus === 'Selesai'
                            ? 'Verifikasi proyek {{ $borongan->kode_borongan }} sebagai Selesai? Status akan terkunci permanen.'
                            : 'Ubah status proyek {{ $borongan->kode_borongan }} menjadi Berjalan? Status tidak dapat dikembalikan ke Menunggu.'"
                        :data-confirm-title="targetStatus === 'Selesai' ? 'Verifikasi Proyek Selesai' : 'Mulai Pengerjaan Proyek'"
                        :data-confirm-button="targetStatus === 'Selesai' ? 'Ya, Tandai Selesai' : 'Ya, Ubah ke Berjalan'"
                        :data-confirm-variant="targetStatus === 'Selesai' ? 'success' : 'primary'"
                    >
                        @csrf
                        @method('PATCH')
                        <select name="status_proyek" x-model="targetStatus" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            @if ($borongan->status_proyek === \App\Enums\StatusProyek::Menunggu)
                                <option value="{{ \App\Enums\StatusProyek::Berjalan->value }}">Berjalan</option>
                            @endif
                            @if (auth()->user()->isPemilik())
                                <option value="{{ \App\Enums\StatusProyek::Selesai->value }}">Selesai (Verifikasi)</option>
                            @endif
                        </select>
                        <button type="submit" class="w-full rounded-xl bg-slate-900 hover:bg-slate-800 text-white py-2 text-sm font-semibold cursor-pointer">Perbarui Status</button>
                    </form>
                @endif
                @if (auth()->user()->isPemilik())
                    <a href="{{ route('penggajian.index') }}" class="block text-center text-xs font-semibold text-amber-600 hover:underline pt-1">
                        Verifikasi &amp; Proses Gaji Tukang →
                    </a>
                @endif
            </div>

            @if ($borongan->jadwalPengiriman)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2 text-sm">
                    <h3 class="font-bold text-slate-900">Jadwal Pengiriman</h3>
                    <p class="font-mono text-xs text-slate-500">{{ $borongan->jadwalPengiriman->kode_jadwal }}</p>
                    <p class="text-slate-700">{{ $borongan->jadwalPengiriman->alamat_pengiriman }}</p>
                    <p class="text-xs text-slate-500">{{ $borongan->jadwalPengiriman->tanggal_kirim->format('d M Y') }} · {{ $borongan->jadwalPengiriman->jam_kirim }}</p>
                    <x-badge color="sky">{{ $borongan->jadwalPengiriman->status_pengiriman->value }}</x-badge>
                </div>
            @endif

            @if ($borongan->penggajianTukangs->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2 text-sm">
                    <h3 class="font-bold text-slate-900">Tukang Terlibat</h3>
                    @foreach ($borongan->penggajianTukangs as $gaji)
                        <div class="border-t border-slate-100 pt-2">
                            <p class="font-semibold text-slate-800">{{ $gaji->karyawanTukang->nama_tukang }}</p>
                            <p class="text-xs text-slate-500">{{ \App\Support\Rupiah::format($gaji->total_gaji) }} · {{ $gaji->status_bayar->value }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

