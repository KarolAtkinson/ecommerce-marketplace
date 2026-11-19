<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Inquiry;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_is_available_after_mfa_confirmation(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();
        Inquiry::query()->create([
            'name' => 'Anna Dashboard',
            'email' => 'anna-dashboard@example.test',
            'company_name' => 'Green Dashboard Shop',
            'status' => 'new',
        ]);
        Inquiry::query()->create([
            'name' => 'Closed Request',
            'email' => 'closed@example.test',
            'status' => 'closed',
        ]);

        $this->actingAs($admin)
            ->withSession(['locale' => 'en'])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('/en-translations.js?v=')
            ->assertSee('Panel administracyjny')
            ->assertSee('Zarządzaj katalogiem')
            ->assertSee('Weryfikacja firm')
            ->assertSee('Anna Dashboard')
            ->assertSee('anna-dashboard@example.test')
            ->assertDontSee('Closed Request');
    }
}
