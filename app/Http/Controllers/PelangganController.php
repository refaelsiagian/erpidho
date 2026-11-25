<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Support\CodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();

        $pelanggans = Pelanggan::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($sub) use ($search): void {
                    $sub->where('nama_pelanggan', 'like', "%{$search}%")
                        ->orWhere('kode_pelanggan', 'like', "%{$search}%")
                        ->orWhere('no_telepon', 'like', "%{$search}%");
                });
            })
            ->orderBy('kode_pelanggan')
            ->paginate(12)
            ->withQueryString();

        return view('master.pelanggans.index', [
            'pelanggans' => $pelanggans,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('master.pelanggans.form', [
            'pelanggan' => new Pelanggan,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePelanggan($request);
        $validated['kode_pelanggan'] = CodeGenerator::next(Pelanggan::class, 'kode_pelanggan', 'PLG', 4);

        Pelanggan::create($validated);

        return redirect()
            ->route('pelanggans.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan): View
    {
        return view('master.pelanggans.form', [
            'pelanggan' => $pelanggan,
        ]);
    }

    public function update(Request $request, Pelanggan $pelanggan): RedirectResponse
    {
        $pelanggan->update($this->validatePelanggan($request));

        return redirect()
            ->route('pelanggans.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan): RedirectResponse
    {
        if ($pelanggan->transaksiPenjualans()->exists() || $pelanggan->pesananBorongans()->exists()) {
            return back()->withErrors([
                'pelanggan' => 'Pelanggan tidak dapat dihapus karena memiliki riwayat transaksi atau borongan.',
            ]);
        }

        $pelanggan->delete();

        return redirect()
            ->route('pelanggans.index')
            ->with('success', 'Data pelanggan berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePelanggan(Request $request): array
    {
        return $request->validate([
            'nama_pelanggan' => ['required', 'string', 'max:60'],
            'no_telepon' => ['nullable', 'string', 'max:15', 'regex:/^[0-9+]+$/'],
            'alamat' => ['nullable', 'string', 'max:150'],
        ]);
    }
}

