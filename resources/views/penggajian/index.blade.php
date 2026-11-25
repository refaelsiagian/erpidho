@php
    $tukangsJson = $tukangs->map(fn ($t) => [
        'id' => $t->id,
        'label' => "{$t->kode_tukang} - {$t->nama_tukang}",
    ])->values();
@endphp

<x-app-layout title="Verifikasi Proyek &amp; Penggajian Tukang">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Verification & Payroll Form --}}
        <section
            class="lg:col-span-1 bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4 self-start"
            x-data="{
                tukangs: @js($tukangsJson),
                assignments: @js(array_values(old('assignments', [['karyawan_tukang_id' => '', 'total_gaji' => 0, 'status_bayar' => 'Belum Dibayar', 'tanggal_dibayar' => now()->toDateString(), 'catatan' => '']]))),
                addRow() {
                    this.assignments.push({ karyawan_tukang_id: '', total_gaji: 0, status_bayar: 'Belum Dibayar', tanggal_dibayar: '{{ now()->toDateString() }}', catatan: '' });
                },
                removeRow(i) {
                    if (this.assignments.length > 1) this.assignments.splice(i, 1);
                }
            }"
        >
            <div>
                <h2 class="font-bold text-slate-900">Verifikasi Proyek &amp; Input Gaji</h2>
                <p class="text-xs text-slate-500">Tugaskan tukang langsung pada penggajian, isi nominal gaji manual, dan tambahkan catatan opsional.</p>
            </div>

            <form method="POST" action="{{ route('penggajian.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Proyek Borongan</label>
                    <select name="pesanan_borongan_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">-- Pilih Pesanan Borongan --</option>
                        @foreach ($borongans as $b)
                            <option value="{{ $b->id }}" @selected(old('pesanan_borongan_id') == $b->id)>
                                {{ $b->kode_borongan }} — {{ $b->pelanggan->nama_pelanggan }} ({{ $b->status_proyek->value }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Diproses</label>
                    <input type="date" name="tanggal_diproses" value="{{ old('tanggal_diproses', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-slate-700">Daftar Tukang &amp; Gaji</label>
                        <button type="button" @click="addRow()" class="text-xs font-semibold text-amber-600 hover:underline cursor-pointer">+ Tambah Tukang</button>
                    </div>

                    <template x-for="(row, index) in assignments" :key="index">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-600" x-text="'Tukang #' + (index + 1)"></span>
                                <button type="button" @click="removeRow(index)" x-show="assignments.length > 1" class="text-xs text-rose-600 font-semibold cursor-pointer">Hapus</button>
                            </div>

                            <select :name="`assignments[${index}][karyawan_tukang_id]`" x-model="row.karyawan_tukang_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                <option value="">-- Pilih Karyawan Tukang --</option>
                                <template x-for="t in tukangs" :key="t.id">
                                    <option :value="t.id" :selected="t.id == row.karyawan_tukang_id" x-text="t.label"></option>
                                </template>
                            </select>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-0.5">Total Gaji (Rp)</label>
                                    <input type="number" :name="`assignments[${index}][total_gaji]`" x-model="row.total_gaji" min="0" required class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-0.5">Status Bayar</label>
                                    <select :name="`assignments[${index}][status_bayar]`" x-model="row.status_bayar" required class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm">
                                        @foreach ($statuses as $st)
                                            <option value="{{ $st->value }}">{{ $st->value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div x-show="row.status_bayar === 'Dibayar'" x-cloak>
                                <label class="block text-[11px] text-slate-500 mb-0.5">Tanggal Dibayar (Status akan terkunci)</label>
                                <input type="date" :name="`assignments[${index}][tanggal_dibayar]`" x-model="row.tanggal_dibayar" max="{{ now()->toDateString() }}" :disabled="row.status_bayar !== 'Dibayar'" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm">
                            </div>

                            <div>
                                <label class="block text-[11px] text-slate-500 mb-0.5">Catatan Pemilik (Opsional)</label>
                                <input type="text" :name="`assignments[${index}][catatan]`" x-model="row.catatan" maxlength="1000" placeholder="Misal: Bonus lembur / potongan kasbon" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm">
                            </div>
                        </div>
                    </template>
                </div>

                <button type="submit" class="w-full rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2.5 text-sm cursor-pointer">
                    Verifikasi Selesai &amp; Simpan Gaji
                </button>
            </form>
        </section>

        {{-- Payroll Recap Table --}}
        <section class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-slate-500 uppercase">Total Gaji Belum Dibayar</p>
                    <p class="text-xl font-bold text-amber-600">{{ \App\Support\Rupiah::format($totalBelumDibayar) }}</p>
                </div>
                <form method="GET" action="{{ route('penggajian.index') }}" class="flex items-center gap-2">
                    <select name="status_bayar" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Semua Status Bayar</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" @selected($selectedStatus === $st->value)>{{ $st->value }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-medium cursor-pointer">Filter</button>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                            <tr>
                                <th class="px-4 py-3">Kode &amp; Proyek</th>
                                <th class="px-4 py-3">Tukang &amp; Catatan</th>
                                <th class="px-4 py-3 text-right">Total Gaji</th>
                                <th class="px-4 py-3">Status Pembayaran</th>
                                <th class="px-4 py-3 text-right">Slip</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($penggajians as $g)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <p class="font-mono text-xs font-semibold text-slate-900">{{ $g->kode_gaji }}</p>
                                        <a href="{{ route('borongan.show', $g->pesananBorongan) }}" class="text-xs text-amber-600 hover:underline">{{ $g->pesananBorongan->kode_borongan }}</a>
                                        <p class="text-[11px] text-slate-400">Diproses: {{ $g->tanggal_diproses->format('d M Y') }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-900">{{ $g->karyawanTukang->nama_tukang }}</p>
                                        @if ($g->catatan)
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $g->catatan }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-900 whitespace-nowrap">{{ \App\Support\Rupiah::format($g->total_gaji) }}</td>
                                    <td class="px-4 py-3">
                                        @if ($g->isDibayar())
                                            <x-badge color="emerald">Dibayar</x-badge>
                                            <p class="text-[11px] text-slate-500 mt-1">Tgl bayar: {{ $g->tanggal_dibayar?->format('d M Y') ?? '-' }}</p>
                                        @else
                                            <form
                                                method="POST"
                                                action="{{ route('penggajian.status', $g) }}"
                                                class="flex flex-wrap items-center gap-2"
                                                data-confirm="Tandai gaji {{ $g->kode_gaji }} sudah dibayar? Status akan terkunci permanen dan masuk laporan keuangan."
                                                data-confirm-title="Konfirmasi Pembayaran Gaji"
                                                data-confirm-button="Ya, Tandai Dibayar"
                                                data-confirm-variant="success"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_bayar" value="{{ \App\Enums\StatusBayarGaji::Dibayar->value }}">
                                                <input type="date" name="tanggal_dibayar" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" class="rounded-lg border border-slate-300 px-2 py-1 text-xs bg-white">
                                                <button type="submit" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 text-xs font-semibold cursor-pointer">
                                                    Tandai Dibayar
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('penggajian.pdf', $g) }}" class="text-xs font-semibold text-slate-700 hover:text-amber-600">Slip PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data penggajian tukang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($penggajians->hasPages())
                    <div class="px-4 py-3 border-t border-slate-100">{{ $penggajians->links() }}</div>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>
