<?php

namespace App\Models;

use App\Enums\StatusKaryawan;
use Database\Factories\KaryawanTukangFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KaryawanTukang extends Model
{
    /** @use HasFactory<KaryawanTukangFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_tukang',
        'nama_tukang',
        'no_telepon',
        'status_karyawan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_karyawan' => StatusKaryawan::class,
        ];
    }

    #[Scope]
    protected function aktif(Builder $query): Builder
    {
        return $query->where('status_karyawan', StatusKaryawan::Aktif);
    }

    public function penggajianTukangs(): HasMany
    {
        return $this->hasMany(PenggajianTukang::class);
    }
}
