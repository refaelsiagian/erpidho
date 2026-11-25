<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\CodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderBy('kode_user')
            ->paginate(12);

        return view('master.users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('master.users.form', [
            'pengguna' => new User(['role' => UserRole::Keluarga]),
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        $validated['kode_user'] = CodeGenerator::next(User::class, 'kode_user', 'USR', 3);

        User::create($validated);

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $pengguna): View
    {
        return view('master.users.form', [
            'pengguna' => $pengguna,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(Request $request, User $pengguna): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('users', 'username')->ignore($pengguna->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        if (blank($validated['password'])) {
            unset($validated['password']);
        }

        $pengguna->update($validated);

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        if ($request->user()->is($pengguna)) {
            return back()->withErrors([
                'user' => 'Anda tidak dapat menghapus akun yang sedang digunakan.',
            ]);
        }

        if (
            $pengguna->transaksiPenjualans()->exists()
            || $pengguna->pesananBorongans()->exists()
            || $pengguna->pengadaans()->exists()
        ) {
            return back()->withErrors([
                'user' => 'Pengguna tidak dapat dihapus karena tercatat dalam riwayat transaksi/pengadaan.',
            ]);
        }

        $pengguna->delete();

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
