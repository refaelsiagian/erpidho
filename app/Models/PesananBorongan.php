<?php

namespace App\Models;

use App\Enums\MetodeBayar;
use App\Enums\StatusPembayaran;
use App\Enums\StatusProyek;
use Database\Factories\PesananBoronganFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PesananBorongan extends Model
{
    /** @use HasFactory<PesananBoronganFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_borongan',
        'pelanggan_id',
        'user_id',
        'tanggal_mulai',
        'tanggal_target',
        'lokasi_proyek',
        'biaya_jasa',
        'biaya_pengiriman',
        'total_biaya',
        'metode_bayar',
        'status_pembayaran',
        'tanggal_lunas',
        'status_proyek',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_target' => 'date',
            'biaya_jasa' => 'integer',
            'biaya_pengiriman' => 'integer',
            'total_biaya' => 'integer',
            'metode_bayar' => MetodeBayar::class,
            'status_pembayaran' => StatusPembayaran::class,
            'tanggal_lunas' => 'date',
            'status_proyek' => StatusProyek::class,
        ];
    }

    public function isLunas(): bool
    {
        return $this->status_pembayaran === StatusPembayaran::Lunas;
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailBorongan::class);
    }

    public function jadwalPengiriman(): HasOne
    {
        return $this->hasOne(JadwalPengiriman::class);
    }

    public function penggajianTukangs(): HasMany
    {
        return $this->hasMany(PenggajianTukang::class);
    }
}
