<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengadaan extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'pengadaan_id',
        'material_id',
        'jumlah_pesan',
        'harga_beli_satuan',
        'subtotal',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah_pesan' => 'integer',
            'harga_beli_satuan' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function pengadaan(): BelongsTo
    {
        return $this->belongsTo(Pengadaan::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
