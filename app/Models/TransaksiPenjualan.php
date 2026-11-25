<?php

namespace App\Models;

use App\Enums\MetodeBayar;
use App\Enums\StatusPembayaran;
use Database\Factories\TransaksiPenjualanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransaksiPenjualan extends Model
{
    /** @use HasFactory<TransaksiPenjualanFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_transaksi',
        'pelanggan_id',
        'user_id',
        'tanggal',
        'biaya_pengiriman',
        'total_bayar',
        'metode_bayar',
        'status_pembayaran',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'biaya_pengiriman' => 'integer',
            'total_bayar' => 'integer',
            'metode_bayar' => MetodeBayar::class,
            'status_pembayaran' => StatusPembayaran::class,
        ];
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
        return $this->hasMany(DetailPenjualan::class);
    }

    public function jadwalPengiriman(): HasOne
    {
        return $this->hasOne(JadwalPengiriman::class);
    }
}
