<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_user',
        'nama_lengkap',
        'username',
        'password',
        'role',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isPemilik(): bool
    {
        return $this->role === UserRole::Pemilik;
    }

    public function transaksiPenjualans(): HasMany
    {
        return $this->hasMany(TransaksiPenjualan::class);
    }

    public function pesananBorongans(): HasMany
    {
        return $this->hasMany(PesananBorongan::class);
    }

    public function pengadaans(): HasMany
    {
        return $this->hasMany(Pengadaan::class);
    }
}
