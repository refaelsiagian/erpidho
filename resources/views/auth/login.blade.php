<x-guest-layout title="Masuk Sistem">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="mx-auto h-12 w-12 rounded-2xl bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-xl shadow-sm">
                RG
            </div>
            <h1 class="text-2xl font-bold text-slate-900">ERPIDHO</h1>
            <p class="text-sm text-slate-500">Sistem Informasi Enterprise Usaha Ridho Gipsum</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.authenticate') }}" class="space-y-4">
            @csrf
            <div>
                <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    value="{{ old('username') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Masukkan username Anda"
                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Kata Sandi</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi"
                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none"
                >
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button
                type="submit"
                class="w-full rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2.5 px-4 text-sm transition cursor-pointer"
            >
                Masuk ke Sistem
            </button>
        </form>

        <div class="border-t border-slate-100 pt-4 text-xs text-slate-500 space-y-1">
            <p class="font-semibold text-slate-600">Akun Demo (Sample):</p>
            <p>Pemilik: <code class="bg-slate-100 px-1.5 py-0.5 rounded">pemilik</code> / <code class="bg-slate-100 px-1.5 py-0.5 rounded">password</code></p>
            <p>Keluarga: <code class="bg-slate-100 px-1.5 py-0.5 rounded">keluarga</code> / <code class="bg-slate-100 px-1.5 py-0.5 rounded">password</code></p>
        </div>
    </div>
</x-guest-layout>

