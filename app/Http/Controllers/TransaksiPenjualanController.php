<?php

namespace App\Http\Controllers;

use App\Enums\MetodeBayar;
use App\Enums\StatusPembayaran;
use App\Models\DetailPenjualan;
use App\Models\Material;
use App\Models\Pelanggan;
use App\Models\TransaksiPenjualan;
use App\Services\StokService;
use App\Support\CodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransaksiPenjualanController extends Controller
{
    public function __construct(public StokService $stokService) {}

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $transaksis = TransaksiPenjualan::query()
            ->with(['pelanggan', 'user'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($sub) use ($search): void {
                    $sub->where('kode_transaksi', 'like', "%{$search}%")
                        ->orWhereHas('pelanggan', fn ($p) => $p->where('nama_pelanggan', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status_pembayaran', $status))
            ->latest('tanggal')
            ->paginate(12)
            ->withQueryString();

        return view('penjualan.index', [
            'transaksis' => $transaksis,
            'statuses' => StatusPembayaran::cases(),
            'search' => $search,
            'selectedStatus' => $status,
        ]);
    }

    public function create(): View
    {
        return view('penjualan.create', [
            'materials' => Material::query()->where('stok_sisa', '>', 0)->orderBy('nama_barang')->get(),
            'pelanggans' => Pelanggan::query()->orderBy('nama_pelanggan')->get(),
            'metodeBayars' => MetodeBayar::cases(),
            'statusPembayarans' => StatusPembayaran::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pelanggan_id' => ['nullable', 'integer', 'exists:pelanggans,id'],
            'nama_pelanggan_baru' => ['nullable', 'string', 'max:60'],
            'no_telepon_baru' => ['nullable', 'string', 'max:15', 'regex:/^[0-9+]+$/'],
            'alamat_baru' => ['nullable', 'string', 'max:150'],
            'metode_bayar' => ['required', Rule::enum(MetodeBayar::class)],
            'status_pembayaran' => ['required', Rule::enum(StatusPembayaran::class)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'integer', 'exists:materials,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Pilih minimal satu material untuk transaksi.',
            'items.min' => 'Pilih minimal satu material untuk transaksi.',
        ]);

        $transaksi = DB::transaction(function () use ($validated, $request): TransaksiPenjualan {
            $pelangganId = $this->stokService->resolvePelanggan(
                isset($validated['pelanggan_id']) ? (int) $validated['pelanggan_id'] : null,
                $validated['nama_pelanggan_baru'] ?? null,
                $validated['no_telepon_baru'] ?? null,
                $validated['alamat_baru'] ?? null,
            );

            $lineItems = array_map(fn (array $row): array => [
                'material_id' => (int) $row['material_id'],
                'qty' => (int) $row['jumlah'],
            ], $validated['items']);

            $preparedItems = $this->stokService->deductMaterials($lineItems);
            $totalBayar = collect($preparedItems)->sum('subtotal');

            $transaksi = TransaksiPenjualan::create([
                'kode_transaksi' => CodeGenerator::next(TransaksiPenjualan::class, 'kode_transaksi', 'TRX', 6),
                'pelanggan_id' => $pelangganId,
                'user_id' => $request->user()->id,
                'tanggal' => now(),
                'biaya_pengiriman' => 0,
                'total_bayar' => $totalBayar,
                'metode_bayar' => $validated['metode_bayar'],
                'status_pembayaran' => $validated['status_pembayaran'],
            ]);

            foreach ($preparedItems as $item) {
                DetailPenjualan::create([
                    'transaksi_penjualan_id' => $transaksi->id,
                    'material_id' => $item['material']->id,
                    'jumlah' => $item['qty'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            return $transaksi;
        });

        return redirect()
            ->route('penjualan.show', $transaksi)
            ->with('success', "Transaksi {$transaksi->kode_transaksi} berhasil dicatat dan stok material diperbarui.");
    }

    public function show(TransaksiPenjualan $penjualan): View
    {
        $penjualan->load(['pelanggan', 'user', 'details.material', 'jadwalPengiriman']);

        return view('penjualan.show', [
            'transaksi' => $penjualan,
        ]);
    }

    public function updateStatus(Request $request, TransaksiPenjualan $penjualan): RedirectResponse
    {
        $validated = $request->validate([
            'status_pembayaran' => ['required', Rule::enum(StatusPembayaran::class)],
        ]);

        $penjualan->update($validated);

        return back()->with('success', 'Status pembayaran transaksi berhasil diperbarui.');
    }

    public function pdf(TransaksiPenjualan $penjualan): Response
    {
        $penjualan->load(['pelanggan', 'user', 'details.material', 'jadwalPengiriman']);

        $pdf = Pdf::loadView('pdf.nota-penjualan', [
            'transaksi' => $penjualan,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("nota-penjualan-{$penjualan->kode_transaksi}.pdf");
    }
}

