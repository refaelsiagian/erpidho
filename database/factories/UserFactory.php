<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_user' => fake()->unique()->numerify('USR-###'),
            'nama_lengkap' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Keluarga,
            'remember_token' => Str::random(10),
        ];
    }

    public function pemilik(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Pemilik,
        ]);
    }

    public function keluarga(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Keluarga,
        ]);
    }
}
