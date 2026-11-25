<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Support\CodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();

        $suppliers = Supplier::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($sub) use ($search): void {
                    $sub->where('nama_supplier', 'like', "%{$search}%")
                        ->orWhere('kode_supplier', 'like', "%{$search}%");
                });
            })
            ->orderBy('kode_supplier')
            ->paginate(12)
            ->withQueryString();

        return view('master.suppliers.index', [
            'suppliers' => $suppliers,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('master.suppliers.form', [
            'supplier' => new Supplier,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSupplier($request);
        $validated['kode_supplier'] = CodeGenerator::next(Supplier::class, 'kode_supplier', 'SUP', 3);

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Data supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('master.suppliers.form', [
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validateSupplier($request));

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Data supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->pengadaans()->exists()) {
            return back()->withErrors([
                'supplier' => 'Supplier tidak dapat dihapus karena memiliki riwayat pengadaan.',
            ]);
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Data supplier berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSupplier(Request $request): array
    {
        return $request->validate([
            'nama_supplier' => ['required', 'string', 'max:80'],
            'no_telepon' => ['nullable', 'string', 'max:15', 'regex:/^[0-9+]+$/'],
            'alamat' => ['nullable', 'string', 'max:150'],
            'keterangan' => ['nullable', 'string', 'max:150'],
        ]);
    }
}

