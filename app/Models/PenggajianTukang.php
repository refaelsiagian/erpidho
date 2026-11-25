<?php

namespace App\Models;

use App\Enums\StatusBayarGaji;
use Database\Factories\PenggajianTukangFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenggajianTukang extends Model
{
    /** @use HasFactory<PenggajianTukangFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_gaji',
        'karyawan_tukang_id',
        'pesanan_borongan_id',
        'tanggal_diproses',
        'tanggal_dibayar',
        'total_gaji',
        'status_bayar',
        'catatan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_diproses' => 'date',
            'tanggal_dibayar' => 'date',
            'total_gaji' => 'integer',
            'status_bayar' => StatusBayarGaji::class,
        ];
    }

    public function isDibayar(): bool
    {
        return $this->status_bayar === StatusBayarGaji::Dibayar;
    }

    public function karyawanTukang(): BelongsTo
    {
        return $this->belongsTo(KaryawanTukang::class);
    }

    public function pesananBorongan(): BelongsTo
    {
        return $this->belongsTo(PesananBorongan::class);
    }
}
