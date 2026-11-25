<?php

namespace App\Http\Controllers;

use App\Enums\MetodeBayar;
use App\Enums\StatusPembayaran;
use App\Enums\StatusProyek;
use App\Models\DetailBorongan;
use App\Models\Material;
use App\Models\Pelanggan;
use App\Models\PesananBorongan;
use App\Services\StokService;
use App\Support\CodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PesananBoronganController extends Controller
{
    public function __construct(public StokService $stokService) {}

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $statusBayar = $request->string('status_bayar')->trim()->toString();

        $borongans = PesananBorongan::query()
            ->with(['pelanggan', 'user'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($sub) use ($search): void {
                    $sub->where('kode_borongan', 'like', "%{$search}%")
                        ->orWhere('lokasi_proyek', 'like', "%{$search}%")
                        ->orWhereHas('pelanggan', fn ($p) => $p->where('nama_pelanggan', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status_proyek', $status))
            ->when($statusBayar !== '', fn ($query) => $query->where('status_pembayaran', $statusBayar))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('borongan.index', [
            'borongans' => $borongans,
            'statuses' => StatusProyek::cases(),
            'paymentStatuses' => StatusPembayaran::cases(),
            'search' => $search,
            'selectedStatus' => $status,
            'selectedPaymentStatus' => $statusBayar,
        ]);
    }

    public function create(): View
    {
        return view('borongan.create', [
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
            'nama_pelanggan_baru' => ['required_without:pelanggan_id', 'nullable', 'string', 'max:60'],
            'no_telepon_baru' => ['nullable', 'string', 'max:15', 'regex:/^[0-9+]+$/'],
            'alamat_baru' => ['nullable', 'string', 'max:150'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_target' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'lokasi_proyek' => ['required', 'string', 'max:150'],
            'biaya_jasa' => ['required', 'integer', 'min:0'],
            'biaya_pengiriman' => ['required', 'integer', 'min:0'],
            'metode_bayar' => ['required', Rule::enum(MetodeBayar::class)],
            'status_pembayaran' => ['required', Rule::enum(StatusPembayaran::class)],
            'tanggal_lunas' => ['nullable', 'date', 'before_or_equal:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'integer', 'exists:materials,id'],
            'items.*.jumlah_material' => ['required', 'integer', 'min:1'],
        ], [
            'nama_pelanggan_baru.required_without' => 'Pilih pelanggan atau masukkan nama pelanggan baru untuk pesanan borongan.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai proyek tidak boleh di masa lalu.',
            'tanggal_target.after_or_equal' => 'Tanggal target harus sama atau setelah tanggal mulai.',
            'tanggal_lunas.before_or_equal' => 'Tanggal lunas tidak boleh melebihi hari ini.',
            'items.required' => 'Pilih minimal satu material untuk pesanan borongan.',
        ]);

        $borongan = DB::transaction(function () use ($validated, $request): PesananBorongan {
            $pelangganId = $this->stokService->resolvePelanggan(
                isset($validated['pelanggan_id']) ? (int) $validated['pelanggan_id'] : null,
                $validated['nama_pelanggan_baru'] ?? null,
                $validated['no_telepon_baru'] ?? null,
                $validated['alamat_baru'] ?? null,
            );

            if ($pelangganId === null) {
                throw ValidationException::withMessages([
                    'pelanggan_id' => 'Pelanggan wajib ditentukan untuk pesanan borongan.',
                ]);
            }

            $lineItems = array_map(fn (array $row): array => [
                'material_id' => (int) $row['material_id'],
                'qty' => (int) $row['jumlah_material'],
            ], $validated['items']);

            $preparedItems = $this->stokService->deductMaterials($lineItems);
            $subtotalMaterial = collect($preparedItems)->sum('subtotal');
            $biayaJasa = (int) $validated['biaya_jasa'];
            $biayaPengiriman = (int) $validated['biaya_pengiriman'];
            $isLunas = $validated['status_pembayaran'] === StatusPembayaran::Lunas->value;

            $borongan = PesananBorongan::create([
                'kode_borongan' => CodeGenerator::next(PesananBorongan::class, 'kode_borongan', 'BRN', 6),
                'pelanggan_id' => $pelangganId,
                'user_id' => $request->user()->id,
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_target' => $validated['tanggal_target'],
                'lokasi_proyek' => $validated['lokasi_proyek'],
                'biaya_jasa' => $biayaJasa,
                'biaya_pengiriman' => $biayaPengiriman,
                'total_biaya' => $subtotalMaterial + $biayaJasa + $biayaPengiriman,
                'metode_bayar' => $validated['metode_bayar'],
                'status_pembayaran' => $validated['status_pembayaran'],
                'tanggal_lunas' => $isLunas ? ($validated['tanggal_lunas'] ?? $validated['tanggal_mulai']) : null,
                'status_proyek' => StatusProyek::Menunggu,
            ]);

            foreach ($preparedItems as $item) {
                DetailBorongan::create([
                    'pesanan_borongan_id' => $borongan->id,
                    'material_id' => $item['material']->id,
                    'jumlah_material' => $item['qty'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal_material' => $item['subtotal'],
                ]);
            }

            return $borongan;
        });

        return redirect()
            ->route('borongan.show', $borongan)
            ->with('success', "Pesanan borongan {$borongan->kode_borongan} berhasil dicatat dan stok material diperbarui.");
    }

    public function show(PesananBorongan $borongan): View
    {
        $borongan->load(['pelanggan', 'user', 'details.material', 'jadwalPengiriman', 'penggajianTukangs.karyawanTukang']);

        return view('borongan.show', [
            'borongan' => $borongan,
            'statuses' => StatusProyek::cases(),
        ]);
    }

    public function updateStatus(Request $request, PesananBorongan $borongan): RedirectResponse
    {
        if ($borongan->status_proyek === StatusProyek::Selesai) {
            return back()->withErrors([
                'status_proyek' => "Proyek {$borongan->kode_borongan} sudah berstatus Selesai dan terkunci.",
            ]);
        }

        $validated = $request->validate([
            'status_proyek' => ['required', Rule::enum(StatusProyek::class)],
        ]);

        $newStatus = StatusProyek::from($validated['status_proyek']);

        if ($newStatus === StatusProyek::Selesai && ! $request->user()->isPemilik()) {
            abort(403, 'Hanya Pemilik yang berwenang memverifikasi proyek selesai.');
        }

        $allowedNext = match ($borongan->status_proyek) {
            StatusProyek::Menunggu => [StatusProyek::Berjalan, StatusProyek::Selesai],
            StatusProyek::Berjalan => [StatusProyek::Selesai],
            StatusProyek::Selesai => [],
        };

        if (! in_array($newStatus, $allowedNext, true)) {
            return back()->withErrors([
                'status_proyek' => "Perubahan status dari {$borongan->status_proyek->value} ke {$newStatus->value} tidak diizinkan.",
            ]);
        }

        $borongan->update(['status_proyek' => $newStatus]);

        return back()->with('success', "Status proyek {$borongan->kode_borongan} diubah menjadi {$newStatus->value}.");
    }

    public function updatePaymentStatus(Request $request, PesananBorongan $borongan): RedirectResponse
    {
        $validated = $request->validate([
            'status_pembayaran' => ['required', Rule::enum(StatusPembayaran::class)],
            'tanggal_lunas' => ['nullable', 'date', 'before_or_equal:today'],
        ], [
            'tanggal_lunas.before_or_equal' => 'Tanggal lunas tidak boleh melebihi hari ini.',
        ]);

        $newStatus = StatusPembayaran::from($validated['status_pembayaran']);

        if (
            $borongan->status_pembayaran === StatusPembayaran::Lunas
            && $newStatus === StatusPembayaran::BelumLunas
            && ! $request->user()->isPemilik()
        ) {
            abort(403, 'Hanya Pemilik yang berwenang mengembalikan status Lunas menjadi Belum Lunas.');
        }

        $borongan->update([
            'status_pembayaran' => $newStatus,
            'tanggal_lunas' => $newStatus === StatusPembayaran::Lunas
                ? ($validated['tanggal_lunas'] ?? now()->toDateString())
                : null,
        ]);

        return back()->with('success', "Status pembayaran {$borongan->kode_borongan} diperbarui.");
    }

    public function pdf(PesananBorongan $borongan): Response
    {
        $borongan->load(['pelanggan', 'user', 'details.material', 'jadwalPengiriman']);

        $pdf = Pdf::loadView('pdf.nota-borongan', [
            'borongan' => $borongan,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("nota-borongan-{$borongan->kode_borongan}.pdf");
    }
}
