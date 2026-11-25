<?php

namespace App\Http\Controllers;

use App\Enums\KategoriMaterial;
use App\Enums\SatuanMaterial;
use App\Models\Material;
use App\Support\CodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $kategori = $request->string('kategori')->trim()->toString();
        $hanyaKritis = $request->boolean('kritis');

        $materials = Material::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($sub) use ($search): void {
                    $sub->where('nama_barang', 'like', "%{$search}%")
                        ->orWhere('kode_barang', 'like', "%{$search}%");
                });
            })
            ->when($kategori !== '', fn ($query) => $query->where('kategori', $kategori))
            ->when($hanyaKritis, fn ($query) => $query->stokKritis())
            ->orderBy('kode_barang')
            ->paginate(12)
            ->withQueryString();

        return view('master.materials.index', [
            'materials' => $materials,
            'kategoris' => KategoriMaterial::cases(),
            'search' => $search,
            'selectedKategori' => $kategori,
            'hanyaKritis' => $hanyaKritis,
        ]);
    }

    public function create(): View
    {
        return view('master.materials.form', [
            'material' => new Material(['batas_minimum' => 5, 'stok_sisa' => 0]),
            'kategoris' => KategoriMaterial::cases(),
            'satuans' => SatuanMaterial::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMaterial($request);
        $validated['kode_barang'] = CodeGenerator::next(Material::class, 'kode_barang', 'BRG', 4);

        Material::create($validated);

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material berhasil ditambahkan.');
    }

    public function edit(Material $material): View
    {
        return view('master.materials.form', [
            'material' => $material,
            'kategoris' => KategoriMaterial::cases(),
            'satuans' => SatuanMaterial::cases(),
        ]);
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $validated = $this->validateMaterial($request);
        $material->update($validated);

        return redirect()
            ->route('materials.index')
            ->with('success', 'Data material berhasil diperbarui.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        if (
            $material->detailPenjualans()->exists()
            || $material->detailBorongans()->exists()
            || $material->detailPengadaans()->exists()
        ) {
            return back()->withErrors([
                'material' => 'Material tidak dapat dihapus karena sudah tercatat pada transaksi atau pengadaan.',
            ]);
        }

        $material->delete();

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMaterial(Request $request): array
    {
        return $request->validate([
            'nama_barang' => ['required', 'string', 'max:100'],
            'kategori' => ['required', Rule::enum(KategoriMaterial::class)],
            'satuan' => ['required', Rule::enum(SatuanMaterial::class)],
            'harga_beli' => ['required', 'integer', 'min:0'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'stok_sisa' => ['required', 'integer', 'min:0'],
            'batas_minimum' => ['required', 'integer', 'min:1'],
        ]);
    }
}

