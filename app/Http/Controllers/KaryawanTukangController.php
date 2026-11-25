<?php

namespace App\Http\Controllers;

use App\Enums\StatusKaryawan;
use App\Models\KaryawanTukang;
use App\Support\CodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KaryawanTukangController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();

        $tukangs = KaryawanTukang::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($sub) use ($search): void {
                    $sub->where('nama_tukang', 'like', "%{$search}%")
                        ->orWhere('kode_tukang', 'like', "%{$search}%");
                });
            })
            ->orderBy('kode_tukang')
            ->paginate(12)
            ->withQueryString();

        return view('master.tukangs.index', [
            'tukangs' => $tukangs,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('master.tukangs.form', [
            'tukang' => new KaryawanTukang(['status_karyawan' => StatusKaryawan::Aktif]),
            'statuses' => StatusKaryawan::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTukang($request);
        $validated['kode_tukang'] = CodeGenerator::next(KaryawanTukang::class, 'kode_tukang', 'TKG', 3);

        KaryawanTukang::create($validated);

        return redirect()
            ->route('tukangs.index')
            ->with('success', 'Data karyawan tukang berhasil ditambahkan.');
    }

    public function edit(KaryawanTukang $tukang): View
    {
        return view('master.tukangs.form', [
            'tukang' => $tukang,
            'statuses' => StatusKaryawan::cases(),
        ]);
    }

    public function update(Request $request, KaryawanTukang $tukang): RedirectResponse
    {
        $tukang->update($this->validateTukang($request));

        return redirect()
            ->route('tukangs.index')
            ->with('success', 'Data karyawan tukang berhasil diperbarui.');
    }

    public function destroy(KaryawanTukang $tukang): RedirectResponse
    {
        if ($tukang->penggajianTukangs()->exists()) {
            return back()->withErrors([
                'tukang' => 'Karyawan tukang tidak dapat dihapus karena memiliki riwayat penggajian.',
            ]);
        }

        $tukang->delete();

        return redirect()
            ->route('tukangs.index')
            ->with('success', 'Data karyawan tukang berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTukang(Request $request): array
    {
        return $request->validate([
            'nama_tukang' => ['required', 'string', 'max:60'],
            'no_telepon' => ['required', 'string', 'max:15', 'regex:/^[0-9+]+$/'],
            'status_karyawan' => ['required', Rule::enum(StatusKaryawan::class)],
        ]);
    }
}
