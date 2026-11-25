<?php

namespace App\Http\Controllers;

use App\Enums\StatusPenerimaan;
use App\Models\DetailPengadaan;
use App\Models\Material;
use App\Models\Pengadaan;
use App\Models\Supplier;
use App\Support\CodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengadaanController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->toString();

        $pengadaans = Pengadaan::query()
            ->with(['supplier', 'user'])
            ->when($status !== '', fn ($query) => $query->where('status_penerimaan', $status))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $stokKritis = Material::query()
            ->stokKritis()
            ->orderBy('stok_sisa')
            ->get();

        return view('pengadaan.index', [
            'pengadaans' => $pengadaans,
            'stokKritis' => $stokKritis,
            'statuses' => StatusPenerimaan::cases(),
            'selectedStatus' => $status,
        ]);
    }

    public function create(Request $request): View
    {
        $prefillKritis = $request->boolean('kritis');

        return view('pengadaan.create', [
            'suppliers' => Supplier::query()->orderBy('nama_supplier')->get(),
            'materials' => Material::query()->orderBy('nama_barang')->get(),
            'kritisMaterials' => $prefillKritis ? Material::query()->stokKritis()->orderBy('stok_sisa')->get() : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'tanggal_pesan' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'integer', 'exists:materials,id'],
            'items.*.jumlah_pesan' => ['required', 'integer', 'min:1'],
            'items.*.harga_beli_satuan' => ['required', 'integer', 'min:0'],
        ], [
            'items.required' => 'Pilih minimal satu material untuk dipesan ke supplier.',
        ]);

        $pengadaan = DB::transaction(function () use ($validated, $request): Pengadaan {
            $totalPengadaan = 0;
            $preparedRows = [];

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['jumlah_pesan'];
                $hargaBeli = (int) $item['harga_beli_satuan'];
                $subtotal = $qty * $hargaBeli;
                $totalPengadaan += $subtotal;

                $preparedRows[] = [
                    'material_id' => (int) $item['material_id'],
                    'jumlah_pesan' => $qty,
                    'harga_beli_satuan' => $hargaBeli,
                    'subtotal' => $subtotal,
                ];
            }

            $pengadaan = Pengadaan::create([
                'kode_pengadaan' => CodeGenerator::next(Pengadaan::class, 'kode_pengadaan', 'PGD', 6),
                'user_id' => $request->user()->id,
                'supplier_id' => (int) $validated['supplier_id'],
                'tanggal_pesan' => $validated['tanggal_pesan'],
                'tanggal_terima' => null,
                'total_pengadaan' => $totalPengadaan,
                'status_penerimaan' => StatusPenerimaan::Dipesan,
            ]);

            foreach ($preparedRows as $row) {
                DetailPengadaan::create([
                    'pengadaan_id' => $pengadaan->id,
                    ...$row,
                ]);
            }

            return $pengadaan;
        });

        return redirect()
            ->route('pengadaan.show', $pengadaan)
            ->with('success', "Pesanan pengadaan {$pengadaan->kode_pengadaan} berhasil dibuat.");
    }

    public function show(Pengadaan $pengadaan): View
    {
        $pengadaan->load(['supplier', 'user', 'details.material']);

        return view('pengadaan.show', [
            'pengadaan' => $pengadaan,
        ]);
    }

    public function receive(Pengadaan $pengadaan): RedirectResponse
    {
        if ($pengadaan->status_penerimaan === StatusPenerimaan::Diterima) {
            return back()->with('warning', "Pengadaan {$pengadaan->kode_pengadaan} sudah pernah dikonfirmasi diterima.");
        }

        DB::transaction(function () use ($pengadaan): void {
            $lockedPengadaan = Pengadaan::with('details')->lockForUpdate()->findOrFail($pengadaan->id);

            if ($lockedPengadaan->status_penerimaan === StatusPenerimaan::Diterima) {
                return;
            }

            foreach ($lockedPengadaan->details as $detail) {
                $material = Material::lockForUpdate()->findOrFail($detail->material_id);
                $material->increment('stok_sisa', $detail->jumlah_pesan);

                if ($detail->harga_beli_satuan > 0) {
                    $material->update(['harga_beli' => $detail->harga_beli_satuan]);
                }
            }

            $lockedPengadaan->update([
                'status_penerimaan' => StatusPenerimaan::Diterima,
                'tanggal_terima' => now()->toDateString(),
            ]);
        });

        return back()->with('success', "Barang dari {$pengadaan->kode_pengadaan} diterima. Stok material bertambah otomatis.");
    }

    public function pdf(Pengadaan $pengadaan): Response
    {
        $pengadaan->load(['supplier', 'user', 'details.material']);

        $pdf = Pdf::loadView('pdf.pesanan-pengadaan', [
            'pengadaan' => $pengadaan,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("daftar-pesanan-bahan-{$pengadaan->kode_pengadaan}.pdf");
    }
}
