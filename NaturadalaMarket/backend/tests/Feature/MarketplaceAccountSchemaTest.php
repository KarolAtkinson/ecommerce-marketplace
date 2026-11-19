<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceAccountSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_seed_is_repeatable_and_does_not_create_an_admin_account(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->assertDatabaseCount('roles', 3);
        $this->assertDatabaseCount('users', 0);
        $this->assertSame('admin', Role::query()->where('name', 'Administrator')->value('slug'));
    }

    public function test_user_can_have_a_pending_retailer_profile_without_approval_privileges(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('slug', 'customer')->firstOrFail());
        $user->retailerProfile()->create([
            'legal_business_name' => 'Example Retail Store',
            'tax_identifier' => 'PL1234567890-SCHEMA-TEST',
        ]);

        $this->assertTrue($user->fresh()->hasRole('customer'));
        $this->assertFalse($user->fresh()->hasRole('admin'));
        $this->assertSame('pending', $user->fresh()->retailerProfile->verification_status);
    }
}
