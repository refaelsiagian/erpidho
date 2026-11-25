<?php

namespace App\Models;

use App\Enums\StatusPengiriman;
use Database\Factories\JadwalPengirimanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPengiriman extends Model
{
    /** @use HasFactory<JadwalPengirimanFactory> */
    use HasFactory;

    protected $table = 'jadwal_pengirimen';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_jadwal',
        'transaksi_penjualan_id',
        'pesanan_borongan_id',
        'alamat_pengiriman',
        'tanggal_kirim',
        'jam_kirim',
        'biaya_pengiriman',
        'status_pengiriman',
        'catatan_bentrok',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_kirim' => 'date',
            'biaya_pengiriman' => 'integer',
            'status_pengiriman' => StatusPengiriman::class,
        ];
    }

    public function transaksiPenjualan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class);
    }

    public function pesananBorongan(): BelongsTo
    {
        return $this->belongsTo(PesananBorongan::class);
    }
}
