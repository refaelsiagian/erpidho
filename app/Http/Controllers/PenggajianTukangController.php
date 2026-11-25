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
use Illuminate\Validation\ValidationException;
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
            'tanggal_diproses' => ['required', 'date', 'before_or_equal:today'],
            'assignments' => ['required', 'array', 'min:1'],
            'assignments.*.karyawan_tukang_id' => ['required', 'integer', 'distinct', 'exists:karyawan_tukangs,id'],
            'assignments.*.total_gaji' => ['required', 'integer', 'min:0'],
            'assignments.*.status_bayar' => ['required', Rule::enum(StatusBayarGaji::class)],
            'assignments.*.tanggal_dibayar' => ['nullable', 'date', 'before_or_equal:today'],
            'assignments.*.catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'tanggal_diproses.before_or_equal' => 'Tanggal diproses tidak boleh melebihi hari ini.',
            'assignments.required' => 'Pilih minimal satu karyawan tukang untuk diproses gajinya.',
            'assignments.*.karyawan_tukang_id.distinct' => 'Karyawan tukang tidak boleh dipilih ganda dalam satu pengajuan.',
            'assignments.*.tanggal_dibayar.before_or_equal' => 'Tanggal dibayar tidak boleh melebihi hari ini.',
        ]);

        $borongan = DB::transaction(function () use ($validated): PesananBorongan {
            $borongan = PesananBorongan::lockForUpdate()->findOrFail($validated['pesanan_borongan_id']);

            $tukangIds = array_map(fn (array $row): int => (int) $row['karyawan_tukang_id'], $validated['assignments']);
            $existingAssignment = PenggajianTukang::query()
                ->with('karyawanTukang')
                ->where('pesanan_borongan_id', $borongan->id)
                ->whereIn('karyawan_tukang_id', $tukangIds)
                ->first();

            if ($existingAssignment !== null) {
                throw ValidationException::withMessages([
                    'assignments' => "Tukang {$existingAssignment->karyawanTukang->nama_tukang} sudah memiliki slip gaji untuk proyek {$borongan->kode_borongan}.",
                ]);
            }

            if ($borongan->status_proyek !== StatusProyek::Selesai) {
                $borongan->update(['status_proyek' => StatusProyek::Selesai]);
            }

            foreach ($validated['assignments'] as $row) {
                $isDibayar = $row['status_bayar'] === StatusBayarGaji::Dibayar->value;
                $tanggalDibayar = $isDibayar
                    ? ($row['tanggal_dibayar'] ?? $validated['tanggal_diproses'])
                    : null;

                PenggajianTukang::create([
                    'kode_gaji' => CodeGenerator::next(PenggajianTukang::class, 'kode_gaji', 'GAJ', 6),
                    'karyawan_tukang_id' => (int) $row['karyawan_tukang_id'],
                    'pesanan_borongan_id' => $borongan->id,
                    'tanggal_diproses' => $validated['tanggal_diproses'],
                    'tanggal_dibayar' => $tanggalDibayar,
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
        if ($penggajian->isDibayar()) {
            return back()->withErrors([
                'status_bayar' => "Gaji {$penggajian->kode_gaji} sudah berstatus Dibayar dan terkunci sehingga tidak dapat diubah kembali.",
            ]);
        }

        $validated = $request->validate([
            'status_bayar' => ['required', Rule::in([StatusBayarGaji::Dibayar->value])],
            'tanggal_dibayar' => ['nullable', 'date', 'before_or_equal:today'],
        ], [
            'status_bayar.in' => 'Status hanya dapat diubah menjadi Dibayar.',
            'tanggal_dibayar.before_or_equal' => 'Tanggal dibayar tidak boleh melebihi hari ini.',
        ]);

        $penggajian->update([
            'status_bayar' => StatusBayarGaji::Dibayar,
            'tanggal_dibayar' => $validated['tanggal_dibayar'] ?? now()->toDateString(),
        ]);

        return back()->with('success', "Gaji {$penggajian->kode_gaji} ditandai Dibayar dan status dikunci.");
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
