<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailBorongan extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'pesanan_borongan_id',
        'material_id',
        'jumlah_material',
        'harga_satuan',
        'subtotal_material',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah_material' => 'integer',
            'harga_satuan' => 'integer',
            'subtotal_material' => 'integer',
        ];
    }

    public function pesananBorongan(): BelongsTo
    {
        return $this->belongsTo(PesananBorongan::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
