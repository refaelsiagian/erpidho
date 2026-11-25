<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_username_and_password(): void
    {
        $user = User::factory()->pemilik()->create([
            'username' => 'ridho_owner',
            'password' => 'secret123',
        ]);

        $response = $this->post(route('login.authenticate'), [
            'username' => 'ridho_owner',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->keluarga()->create([
            'username' => 'siti_keluarga',
            'password' => 'secret123',
        ]);

        $response = $this->post(route('login.authenticate'), [
            'username' => 'siti_keluarga',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public static function pemilikOnlyRoutes(): array
    {
        return [
            'penggajian' => ['/penggajian'],
            'laporan' => ['/laporan'],
            'tukangs' => ['/tukangs'],
            'suppliers' => ['/suppliers'],
            'pengguna' => ['/pengguna'],
            'materials create' => ['/materials/create'],
        ];
    }

    #[DataProvider('pemilikOnlyRoutes')]
    public function test_keluarga_is_forbidden_from_pemilik_only_routes(string $uri): void
    {
        $keluarga = User::factory()->keluarga()->create();

        $this->actingAs($keluarga)
            ->get($uri)
            ->assertForbidden();
    }

    public function test_keluarga_can_access_daily_operational_routes(): void
    {
        $keluarga = User::factory()->keluarga()->create();

        $this->actingAs($keluarga)->get(route('dashboard'))->assertOk();
        $this->actingAs($keluarga)->get(route('penjualan.index'))->assertOk();
        $this->actingAs($keluarga)->get(route('borongan.index'))->assertOk();
        $this->actingAs($keluarga)->get(route('pengiriman.index'))->assertOk();
        $this->actingAs($keluarga)->get(route('pengadaan.index'))->assertOk();
        $this->actingAs($keluarga)->get(route('materials.index'))->assertOk();
        $this->actingAs($keluarga)->get(route('pelanggans.index'))->assertOk();
    }

    public function test_all_authenticated_and_guest_pages_render_successfully(): void
    {
        $this->get(route('login'))->assertOk();

        $pemilik = User::factory()->pemilik()->create();

        foreach ([
            route('dashboard'),
            route('materials.index'),
            route('materials.create'),
            route('pelanggans.index'),
            route('pelanggans.create'),
            route('tukangs.index'),
            route('tukangs.create'),
            route('suppliers.index'),
            route('suppliers.create'),
            route('pengguna.index'),
            route('pengguna.create'),
            route('penjualan.index'),
            route('penjualan.create'),
            route('borongan.index'),
            route('borongan.create'),
            route('pengiriman.index'),
            route('pengiriman.create'),
            route('pengadaan.index'),
            route('pengadaan.create'),
            route('penggajian.index'),
            route('laporan.index'),
        ] as $url) {
            $this->actingAs($pemilik)->get($url)->assertOk();
        }
    }
}
