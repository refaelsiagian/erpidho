<?php

namespace App\Models;

use Database\Factories\PelangganFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    /** @use HasFactory<PelangganFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_pelanggan',
        'nama_pelanggan',
        'no_telepon',
        'alamat',
    ];

    public function transaksiPenjualans(): HasMany
    {
        return $this->hasMany(TransaksiPenjualan::class);
    }

    public function pesananBorongans(): HasMany
    {
        return $this->hasMany(PesananBorongan::class);
    }
}
