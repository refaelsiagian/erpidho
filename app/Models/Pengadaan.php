<?php

namespace App\Models;

use App\Enums\StatusPenerimaan;
use Database\Factories\PengadaanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengadaan extends Model
{
    /** @use HasFactory<PengadaanFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_pengadaan',
        'user_id',
        'supplier_id',
        'tanggal_pesan',
        'tanggal_terima',
        'total_pengadaan',
        'status_penerimaan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pesan' => 'date',
            'tanggal_terima' => 'date',
            'total_pengadaan' => 'integer',
            'status_penerimaan' => StatusPenerimaan::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPengadaan::class);
    }
}
