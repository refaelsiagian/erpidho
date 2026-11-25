<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — ERPIDHO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-screen bg-slate-50 text-slate-900 antialiased"
    x-data="{
        sidebarOpen: false,
        confirmModal: {
            open: false,
            title: '',
            message: '',
            confirmText: 'Ya, Lanjutkan',
            variant: 'primary',
            form: null,
        },
        openConfirm(detail) {
            this.confirmModal = {
                open: true,
                title: detail.title || 'Konfirmasi Tindakan',
                message: detail.message || '',
                confirmText: detail.confirmText || 'Ya, Lanjutkan',
                variant: detail.variant || 'primary',
                form: detail.form || null,
            };
        },
        closeConfirm() {
            this.confirmModal.open = false;
            this.confirmModal.form = null;
        },
        proceedConfirm() {
            const targetForm = this.confirmModal.form;
            this.confirmModal.open = false;
            this.confirmModal.form = null;
            if (targetForm) {
                targetForm.dataset.confirmed = '1';
                targetForm.requestSubmit();
            }
        }
    }"
    @erphido-confirm.window="openConfirm($event.detail)"
    @keydown.escape.window="if (confirmModal.open) closeConfirm()"
>
    <div class="min-h-screen flex">
        {{-- Mobile backdrop --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
        ></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-100 flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:inset-auto"
        >
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-lg shadow-sm">
                        RG
                    </div>
                    <div>
                        <p class="font-bold tracking-tight text-white leading-tight">ERPIDHO</p>
                        <p class="text-xs text-slate-400">Usaha Ridho Gipsum</p>
                    </div>
                </a>
                <button
                    type="button"
                    @click="sidebarOpen = false"
                    class="lg:hidden rounded-lg p-1.5 text-slate-400 hover:text-white hover:bg-slate-800"
                    aria-label="Tutup menu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @php
                $isPemilik = auth()->user()?->isPemilik() ?? false;
                $stokKritisCount = \App\Models\Material::query()->stokKritis()->count();
            @endphp

            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6 text-sm">
                <div>
                    <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Utama</p>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>Dashboard</span>
                    </a>
                </div>

                <div>
                    <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Penjualan &amp; Operasional</p>
                    <div class="space-y-1">
                        <a href="{{ route('penjualan.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('penjualan.*') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>Penjualan Langsung (POS)</span>
                        </a>
                        <a href="{{ route('borongan.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('borongan.*') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>Pesanan Borongan</span>
                        </a>
                        <a href="{{ route('pengiriman.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('pengiriman.*') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>Jadwal Pengiriman</span>
                        </a>
                        <a href="{{ route('pengadaan.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('pengadaan.*') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>Pengadaan &amp; Stok</span>
                            @if ($stokKritisCount > 0)
                                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-rose-500 text-white">{{ $stokKritisCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                <div>
                    <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Data Master</p>
                    <div class="space-y-1">
                        <a href="{{ route('materials.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('materials.*') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>Material</span>
                        </a>
                        <a href="{{ route('pelanggans.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('pelanggans.*') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>Pelanggan</span>
                        </a>
                        @if ($isPemilik)
                            <a href="{{ route('tukangs.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('tukangs.*') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span>Karyawan Tukang</span>
                            </a>
                            <a href="{{ route('suppliers.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('suppliers.*') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span>Supplier</span>
                            </a>
                            <a href="{{ route('pengguna.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('pengguna.*') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span>Pengguna Sistem</span>
                            </a>
                        @endif
                    </div>
                </div>

                @if ($isPemilik)
                    <div>
                        <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Khusus Pemilik</p>
                        <div class="space-y-1">
                            <a href="{{ route('penggajian.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('penggajian.*') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span>Verifikasi &amp; Gaji Tukang</span>
                            </a>
                            <a href="{{ route('laporan.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('laporan.*') ? 'bg-amber-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span>Laporan Operasional</span>
                            </a>
                        </div>
                    </div>
                @endif
            </nav>

            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->nama_lengkap }}</p>
                        <p class="text-xs text-slate-400">Peran: <span class="text-amber-400 font-semibold">{{ auth()->user()->role->value }}</span></p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-800 text-slate-200 hover:bg-rose-600 hover:text-white transition">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="sticky top-0 z-30 bg-white border-b border-slate-200 px-4 sm:px-6 py-3.5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="lg:hidden rounded-lg p-2 text-slate-600 hover:bg-slate-100"
                        aria-label="Buka menu"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-lg sm:text-xl font-bold text-slate-900 truncate">{{ $title ?? 'Dashboard' }}</h1>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('penjualan.create') }}" class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-semibold transition">
                        + Transaksi Kasir
                    </a>
                    <a href="{{ route('borongan.create') }}" class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">
                        + Pesanan Borongan
                    </a>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto space-y-6">
                @if (session('success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-start justify-between gap-3">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        {{ session('warning') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <p class="font-semibold mb-1">Terdapat kesalahan input:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Global Confirmation Modal --}}
    <div
        x-show="confirmModal.open"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-modal-title"
    >
        <div
            @click.outside="closeConfirm()"
            x-transition.scale.95
            class="w-full max-w-md rounded-2xl bg-white border border-slate-200 shadow-xl p-6 space-y-4"
        >
            <div class="flex items-start gap-3">
                <div
                    class="h-10 w-10 rounded-xl flex items-center justify-center shrink-0"
                    :class="{
                        'bg-rose-100 text-rose-600': confirmModal.variant === 'danger',
                        'bg-emerald-100 text-emerald-600': confirmModal.variant === 'success',
                        'bg-amber-100 text-amber-600': confirmModal.variant === 'warning',
                        'bg-slate-100 text-slate-700': confirmModal.variant === 'primary',
                    }"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 id="confirm-modal-title" class="text-base font-bold text-slate-900" x-text="confirmModal.title"></h3>
                    <p class="text-sm text-slate-600 leading-relaxed" x-text="confirmModal.message"></p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button
                    type="button"
                    @click="closeConfirm()"
                    class="rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 px-4 py-2 text-sm font-medium cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="proceedConfirm()"
                    class="rounded-xl px-4 py-2 text-sm font-semibold cursor-pointer transition"
                    :class="{
                        'bg-rose-600 hover:bg-rose-700 text-white': confirmModal.variant === 'danger',
                        'bg-emerald-600 hover:bg-emerald-700 text-white': confirmModal.variant === 'success',
                        'bg-amber-500 hover:bg-amber-600 text-slate-950': confirmModal.variant === 'warning',
                        'bg-slate-900 hover:bg-slate-800 text-white': confirmModal.variant === 'primary',
                    }"
                    x-text="confirmModal.confirmText"
                ></button>
            </div>
        </div>
    </div>
</body>
</html>

