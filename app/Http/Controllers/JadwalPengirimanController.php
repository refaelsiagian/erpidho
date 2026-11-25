<?php

namespace App\Http\Controllers;

use App\Enums\StatusPengiriman;
use App\Models\JadwalPengiriman;
use App\Models\PesananBorongan;
use App\Models\TransaksiPenjualan;
use App\Support\CodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JadwalPengirimanController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->toString();

        $jadwals = JadwalPengiriman::query()
            ->with(['transaksiPenjualan.pelanggan', 'pesananBorongan.pelanggan'])
            ->when($status !== '', fn ($query) => $query->where('status_pengiriman', $status))
            ->orderByDesc('tanggal_kirim')
            ->orderBy('jam_kirim')
            ->paginate(12)
            ->withQueryString();

        return view('pengiriman.index', [
            'jadwals' => $jadwals,
            'statuses' => StatusPengiriman::cases(),
            'selectedStatus' => $status,
        ]);
    }

    public function create(Request $request): View
    {
        $transaksis = TransaksiPenjualan::query()
            ->with('pelanggan')
            ->where('perlu_pengiriman', true)
            ->whereDoesntHave('jadwalPengiriman')
            ->latest('tanggal')
            ->get();

        $borongans = PesananBorongan::query()
            ->with('pelanggan')
            ->whereDoesntHave('jadwalPengiriman')
            ->latest('id')
            ->get();

        return view('pengiriman.create', [
            'transaksis' => $transaksis,
            'borongans' => $borongans,
            'preselectType' => $request->string('jenis')->toString(),
            'preselectId' => $request->integer('id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_referensi' => ['required', Rule::in(['penjualan', 'borongan'])],
            'referensi_id' => ['required', 'integer', 'min:1'],
            'alamat_pengiriman' => ['required', 'string', 'max:150'],
            'tanggal_kirim' => ['required', 'date', 'after_or_equal:today'],
            'jam_kirim' => ['required', 'date_format:H:i'],
            'confirm_buffer_warning' => ['nullable', 'boolean'],
        ], [
            'tanggal_kirim.after_or_equal' => 'Tanggal pengiriman tidak boleh di masa lalu.',
        ]);

        if ($validated['tanggal_kirim'] === now()->toDateString() && $validated['jam_kirim'] < now()->format('H:i')) {
            throw ValidationException::withMessages([
                'jam_kirim' => 'Jam pengiriman hari ini tidak boleh lebih awal dari waktu sekarang.',
            ]);
        }

        $transaksiId = $validated['jenis_referensi'] === 'penjualan' ? (int) $validated['referensi_id'] : null;
        $boronganId = $validated['jenis_referensi'] === 'borongan' ? (int) $validated['referensi_id'] : null;
        $biayaPengiriman = 0;

        if ($transaksiId !== null) {
            $transaksi = TransaksiPenjualan::findOrFail($transaksiId);
            if (! $transaksi->perlu_pengiriman) {
                return back()->withInput()->withErrors([
                    'referensi_id' => "Transaksi {$transaksi->kode_transaksi} tidak memilih opsi pengiriman.",
                ]);
            }
            if ($transaksi->jadwalPengiriman()->exists()) {
                return back()->withInput()->withErrors([
                    'referensi_id' => "Transaksi {$transaksi->kode_transaksi} sudah memiliki jadwal pengiriman.",
                ]);
            }
            $biayaPengiriman = $transaksi->biaya_pengiriman;
        }

        if ($boronganId !== null) {
            $borongan = PesananBorongan::findOrFail($boronganId);
            if ($borongan->jadwalPengiriman()->exists()) {
                return back()->withInput()->withErrors([
                    'referensi_id' => "Pesanan {$borongan->kode_borongan} sudah memiliki jadwal pengiriman.",
                ]);
            }
            $biayaPengiriman = $borongan->biaya_pengiriman;
        }

        $conflictResult = $this->checkScheduleConflict($validated['tanggal_kirim'], $validated['jam_kirim']);

        if ($conflictResult['blocked'] !== null) {
            throw ValidationException::withMessages([
                'jam_kirim' => $conflictResult['blocked'],
            ]);
        }

        if ($conflictResult['warning'] !== null && ! $request->boolean('confirm_buffer_warning')) {
            return back()
                ->withInput()
                ->with('buffer_warning', $conflictResult['warning']);
        }

        $jadwal = DB::transaction(function () use ($validated, $transaksiId, $boronganId, $biayaPengiriman, $conflictResult): JadwalPengiriman {
            return JadwalPengiriman::create([
                'kode_jadwal' => CodeGenerator::next(JadwalPengiriman::class, 'kode_jadwal', 'JDW', 6),
                'transaksi_penjualan_id' => $transaksiId,
                'pesanan_borongan_id' => $boronganId,
                'alamat_pengiriman' => $validated['alamat_pengiriman'],
                'tanggal_kirim' => $validated['tanggal_kirim'],
                'jam_kirim' => $validated['jam_kirim'],
                'biaya_pengiriman' => $biayaPengiriman,
                'status_pengiriman' => StatusPengiriman::Terjadwal,
                'catatan_bentrok' => $conflictResult['warning'],
            ]);
        });

        return redirect()
            ->route('pengiriman.index')
            ->with('success', "Jadwal pengiriman {$jadwal->kode_jadwal} berhasil dibuat.");
    }

    public function updateStatus(Request $request, JadwalPengiriman $pengiriman): RedirectResponse
    {
        if ($pengiriman->status_pengiriman === StatusPengiriman::Selesai) {
            return back()->withErrors([
                'status_pengiriman' => "Pengiriman {$pengiriman->kode_jadwal} sudah Selesai dan terkunci.",
            ]);
        }

        $validated = $request->validate([
            'status_pengiriman' => ['required', Rule::enum(StatusPengiriman::class)],
        ]);

        $newStatus = StatusPengiriman::from($validated['status_pengiriman']);

        $expectedNext = match ($pengiriman->status_pengiriman) {
            StatusPengiriman::Terjadwal => StatusPengiriman::Dikirim,
            StatusPengiriman::Dikirim => StatusPengiriman::Selesai,
            StatusPengiriman::Selesai => null,
        };

        if ($newStatus !== $expectedNext) {
            return back()->withErrors([
                'status_pengiriman' => "Perubahan status pengiriman dari {$pengiriman->status_pengiriman->value} ke {$newStatus->value} tidak diizinkan.",
            ]);
        }

        $pengiriman->update(['status_pengiriman' => $newStatus]);

        return back()->with('success', "Status pengiriman {$pengiriman->kode_jadwal} diperbarui menjadi {$newStatus->value}.");
    }

    /**
     * Check exact-time conflict (hard block) and +/- 60-minute buffer conflict (warning).
     *
     * @return array{blocked: ?string, warning: ?string}
     */
    private function checkScheduleConflict(string $tanggalKirim, string $jamKirim): array
    {
        $existingSchedules = JadwalPengiriman::query()
            ->with(['transaksiPenjualan', 'pesananBorongan'])
            ->whereDate('tanggal_kirim', $tanggalKirim)
            ->where('status_pengiriman', '!=', StatusPengiriman::Selesai->value)
            ->get();

        $targetMinutes = $this->timeToMinutes($jamKirim);
        $bufferNotes = [];

        foreach ($existingSchedules as $existing) {
            $existingMinutes = $this->timeToMinutes($existing->jam_kirim);
            $diffMinutes = abs($targetMinutes - $existingMinutes);
            $refCode = $existing->pesananBorongan?->kode_borongan ?? $existing->transaksiPenjualan?->kode_transaksi ?? '-';

            if ($diffMinutes === 0) {
                return [
                    'blocked' => "Jadwal diblokir: bentrok tepat waktu pada {$existing->tanggal_kirim->format('d M Y')} pukul {$existing->jam_kirim} dengan {$existing->kode_jadwal} ({$refCode} - {$existing->alamat_pengiriman}).",
                    'warning' => null,
                ];
            }

            if ($diffMinutes <= 60) {
                $bufferNotes[] = "{$existing->kode_jadwal} ({$refCode}) pukul {$existing->jam_kirim} ke {$existing->alamat_pengiriman}";
            }
        }

        if ($bufferNotes !== []) {
            return [
                'blocked' => null,
                'warning' => 'Peringatan selisih waktu <= 1 jam dengan jadwal lain: '.implode('; ', $bufferNotes).'. Centang konfirmasi di bawah jika tetap ingin menjadwalkan.',
            ];
        }

        return ['blocked' => null, 'warning' => null];
    }

    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return ($hours * 60) + $minutes;
    }
}
