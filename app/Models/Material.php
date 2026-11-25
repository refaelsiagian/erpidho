<?php

namespace App\Models;

use App\Enums\KategoriMaterial;
use App\Enums\SatuanMaterial;
use Database\Factories\MaterialFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    /** @use HasFactory<MaterialFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok_sisa',
        'batas_minimum',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kategori' => KategoriMaterial::class,
            'satuan' => SatuanMaterial::class,
            'harga_beli' => 'integer',
            'harga_jual' => 'integer',
            'stok_sisa' => 'integer',
            'batas_minimum' => 'integer',
        ];
    }

    #[Scope]
    protected function stokKritis(Builder $query): Builder
    {
        return $query->whereColumn('stok_sisa', '<=', 'batas_minimum');
    }

    public function isStokKritis(): bool
    {
        return $this->stok_sisa <= $this->batas_minimum;
    }

    public function detailPenjualans(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class);
    }

    public function detailBorongans(): HasMany
    {
        return $this->hasMany(DetailBorongan::class);
    }

    public function detailPengadaans(): HasMany
    {
        return $this->hasMany(DetailPengadaan::class);
    }
}
