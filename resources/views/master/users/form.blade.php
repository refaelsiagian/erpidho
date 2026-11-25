@php($isEdit = $pengguna->exists)
<x-app-layout :title="$isEdit ? 'Ubah Pengguna' : 'Tambah Pengguna'">
    <div class="max-w-xl bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <form method="POST" action="{{ $isEdit ? route('pengguna.update', $pengguna) : route('pengguna.store') }}" class="space-y-4">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $pengguna->nama_lengkap) }}" required maxlength="50" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username', $pengguna->username) }}" required maxlength="30" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Kata Sandi {{ $isEdit ? '(Kosongkan jika tidak diubah)' : '' }}
                </label>
                <input type="password" name="password" {{ $isEdit ? '' : 'required' }} minlength="6" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Peran (Role)</label>
                <select name="role" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-amber-500 outline-none">
                    @foreach ($roles as $r)
                        <option value="{{ $r->value }}" @selected(old('role', $pengguna->role?->value) === $r->value)>{{ $r->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-5 py-2.5 text-sm cursor-pointer">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' }}</button>
                <a href="{{ route('pengguna.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>

