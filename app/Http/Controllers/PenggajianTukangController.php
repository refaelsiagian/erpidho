<?php

namespace App\Http\Controllers;

use App\Enums\StatusBayarGaji;
use App\Enums\StatusKaryawan;
use App\Enums\StatusProyek;
use App\Models\KaryawanTukang;
use App\Models\PenggajianTukang;
use App\Models\PesananBorongan;
use App\Support\CodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenggajianTukangController extends Controller
{
    public function index(Request $request): View
    {
        $statusBayar = $request->string('status_bayar')->trim()->toString();

        $borongans = PesananBorongan::query()
            ->with(['pelanggan', 'penggajianTukangs.karyawanTukang'])
            ->latest('id')
            ->get();

        $penggajians = PenggajianTukang::query()
            ->with(['karyawanTukang', 'pesananBorongan.pelanggan'])
            ->when($statusBayar !== '', fn ($query) => $query->where('status_bayar', $statusBayar))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $tukangs = KaryawanTukang::query()
            ->where('status_karyawan', StatusKaryawan::Aktif)
            ->orderBy('nama_tukang')
            ->get();

        $totalBelumDibayar = (int) PenggajianTukang::query()
            ->where('status_bayar', StatusBayarGaji::BelumDibayar)
            ->sum('total_gaji');

        return view('penggajian.index', [
            'borongans' => $borongans,
            'penggajians' => $penggajians,
            'tukangs' => $tukangs,
            'statuses' => StatusBayarGaji::cases(),
            'selectedStatus' => $statusBayar,
            'totalBelumDibayar' => $totalBelumDibayar,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pesanan_borongan_id' => ['required', 'integer', 'exists:pesanan_borongans,id'],
            'tanggal_diproses' => ['required', 'date'],
            'assignments' => ['required', 'array', 'min:1'],
            'assignments.*.karyawan_tukang_id' => ['required', 'integer', 'exists:karyawan_tukangs,id'],
            'assignments.*.total_gaji' => ['required', 'integer', 'min:0'],
            'assignments.*.status_bayar' => ['required', Rule::enum(StatusBayarGaji::class)],
            'assignments.*.catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'assignments.required' => 'Pilih minimal satu karyawan tukang untuk diproses gajinya.',
        ]);

        $borongan = DB::transaction(function () use ($validated): PesananBorongan {
            $borongan = PesananBorongan::lockForUpdate()->findOrFail($validated['pesanan_borongan_id']);

            if ($borongan->status_proyek !== StatusProyek::Selesai) {
                $borongan->update(['status_proyek' => StatusProyek::Selesai]);
            }

            foreach ($validated['assignments'] as $row) {
                PenggajianTukang::create([
                    'kode_gaji' => CodeGenerator::next(PenggajianTukang::class, 'kode_gaji', 'GAJ', 6),
                    'karyawan_tukang_id' => (int) $row['karyawan_tukang_id'],
                    'pesanan_borongan_id' => $borongan->id,
                    'tanggal_diproses' => $validated['tanggal_diproses'],
                    'total_gaji' => (int) $row['total_gaji'],
                    'status_bayar' => $row['status_bayar'],
                    'catatan' => isset($row['catatan']) && trim((string) $row['catatan']) !== '' ? trim((string) $row['catatan']) : null,
                ]);
            }

            return $borongan;
        });

        return redirect()
            ->route('penggajian.index')
            ->with('success', "Proyek {$borongan->kode_borongan} terverifikasi Selesai dan gaji tukang berhasil diproses.");
    }

    public function updateStatus(Request $request, PenggajianTukang $penggajian): RedirectResponse
    {
        $validated = $request->validate([
            'status_bayar' => ['required', Rule::enum(StatusBayarGaji::class)],
        ]);

        $penggajian->update($validated);

        return back()->with('success', "Status pembayaran gaji {$penggajian->kode_gaji} diperbarui.");
    }

    public function pdf(PenggajianTukang $penggajian): Response
    {
        $penggajian->load(['karyawanTukang', 'pesananBorongan.pelanggan']);

        $pdf = Pdf::loadView('pdf.slip-gaji', [
            'gaji' => $penggajian,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("slip-gaji-{$penggajian->kode_gaji}.pdf");
    }
}

