<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'no_telepon',
        'alamat',
        'keterangan',
    ];

    public function pengadaans(): HasMany
    {
        return $this->hasMany(Pengadaan::class);
    }
}
