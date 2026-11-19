<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\RetailerContract;
use App\Models\RetailerProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\WholesalePrice;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class RetailerWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $admin;
    }

    private function makeRetailer(string $verificationStatus = 'approved'): array
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $profile = RetailerProfile::query()->create([
            'user_id' => $user->id,
            'legal_business_name' => 'Example Retail Company',
            'tax_identifier' => 'PL'.str_pad((string) $user->id, 10, '0', STR_PAD_LEFT),
            'verification_status' => $verificationStatus,
        ]);

        if ($verificationStatus === 'approved') {
            $user->roles()->attach(Role::query()->where('slug', 'retailer')->firstOrFail());
        }

        return [$user, $profile];
    }

    private function makeProduct(): Product
    {
        return Product::query()->create([
            'sku' => 'WORKSPACE-001',
            'slug' => 'workspace-product',
            'name' => 'Workspace test product',
            'short_description' => 'A test product for the retailer workspace.',
            'description' => 'Test description.',
            'minimum_order_quantity' => 2,
            'active' => true,
        ]);
    }

    public function test_only_approved_retailers_can_access_the_separate_workspace(): void
    {
        $product = $this->makeProduct();
        WholesalePrice::query()->create([
            'product_id' => $product->id,
            'unit_price' => '15.25',
            'minimum_quantity' => 2,
        ]);
        [$pendingUser] = $this->makeRetailer('pending');
        [$approvedUser] = $this->makeRetailer();

        $this->get(route('retailer.workspace'))->assertRedirect(route('login'));

        $this->actingAs($pendingUser)
            ->get(route('retailer.workspace'))
            ->assertForbidden();

        $this->actingAs($approvedUser)
            ->get(route('retailer.workspace'))
            ->assertOk()
            ->assertSee('<header class="site-header">', false)
            ->assertSee('aria-controls="primary-navigation"', false)
            ->assertSee('id="basket-popup"', false)
            ->assertSee('aria-label="Nawigacja partnera"', false)
            ->assertSee('Wyloguj się')
            ->assertSee('Katalog hurtowy')
            ->assertSee('Example Retail Company')
            ->assertSee('Workspace test product')
            ->assertSee('15,25 zł')
            ->assertSee('Dodaj do zapytania')
            ->assertSee('Katalog hurtowy');
    }

    public function test_successful_approved_retailer_sign_in_defaults_to_the_partner_workspace(): void
    {
        [$retailer] = $this->makeRetailer();

        $this->post(route('login.store'), [
            'email' => $retailer->email,
            'password' => 'password',
        ])->assertRedirect(route('retailer.workspace'));

        $this->assertAuthenticatedAs($retailer);
    }

    public function test_approved_retailer_with_mfa_signs_in_to_the_partner_workspace(): void
    {
        $google2fa = new Google2FA;
        [$retailer] = $this->makeRetailer();
        $secret = $google2fa->generateSecretKey();
        $retailer->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->post(route('login.store'), [
            'email' => $retailer->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.login'));

        $this->post(route('two-factor.login.store'), [
            'code' => $google2fa->getCurrentOtp($secret),
        ])->assertRedirect(route('retailer.workspace'));

        $this->assertAuthenticatedAs($retailer);
    }

    public function test_admin_can_manage_retailer_access_without_deleting_the_company_record(): void
    {
        $admin = $this->makeAdmin();
        [$retailer, $profile] = $this->makeRetailer();

        $this->actingAs($admin)
            ->get(route('admin.retailers.directory'))
            ->assertOk()
            ->assertSee('Example Retail Company')
            ->assertSee('Odbierz dostęp hurtowy');

        $this->patch(route('admin.retailers.access.update', $profile), [
            'action' => 'suspend',
            'reason' => 'Reviewing company account details.',
        ])->assertRedirect();

        $this->assertSame('suspended', $retailer->fresh()->status);
        $this->assertNotNull($retailer->fresh()->suspended_at);
        $this->assertDatabaseHas('security_events', [
            'user_id' => $retailer->id,
            'event_type' => 'retailer_suspended',
        ]);

        $this->actingAs($retailer->fresh())
            ->get(route('retailer.workspace'))
            ->assertRedirect(route('login'));

        $this->actingAs($admin)
            ->patch(route('admin.retailers.access.update', $profile), [
                'action' => 'reactivate',
            ])->assertRedirect();

        $this->assertSame('active', $retailer->fresh()->status);
        $this->assertNull($retailer->fresh()->suspended_at);

        $this->patch(route('admin.retailers.access.update', $profile), [
            'action' => 'revoke',
            'reason' => 'Wholesale relationship ended.',
        ])->assertRedirect();

        $this->assertSame('rejected', $profile->fresh()->verification_status);
        $this->assertFalse($retailer->fresh()->hasRole('retailer'));
        $this->assertDatabaseHas('security_events', [
            'user_id' => $retailer->id,
            'event_type' => 'retailer_access_revoked',
        ]);
        $this->assertDatabaseHas('retailer_profiles', ['id' => $profile->id]);
    }

    public function test_admin_can_create_update_and_remove_contract_metadata_records(): void
    {
        $admin = $this->makeAdmin();
        [$retailer, $profile] = $this->makeRetailer();

        $this->actingAs($admin)
            ->get(route('admin.retailers.contracts.index'))
            ->assertOk()
            ->assertSee('Example Retail Ltd')
            ->assertSee('DEMO-0001')
            ->assertSee('fikcyjne', false);

        $payload = [
            'retailer_profile_id' => $profile->id,
            'reference' => 'AGR-EXAMPLE-001',
            'title' => 'Wholesale cooperation record',
            'contract_type' => 'Wholesale agreement',
            'internal_owner_id' => $admin->id,
            'effective_date' => '2026-11-01',
            'expiry_date' => '2027-10-31',
            'status' => 'signed',
            'notice_period_days' => 30,
        ];

        $this->post(route('admin.retailers.contracts.store'), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $contract = RetailerContract::query()->firstOrFail();
        $this->assertSame('AGR-EXAMPLE-001', $contract->reference);
        $this->assertDatabaseHas('security_events', [
            'user_id' => $retailer->id,
            'event_type' => 'retailer_contract_created',
        ]);

        $this->actingAs($retailer->fresh())
            ->get(route('retailer.workspace'))
            ->assertOk()
            ->assertSee('AGR-EXAMPLE-001')
            ->assertSee('Wholesale cooperation record');

        $this->actingAs($admin)
            ->patch(route('admin.retailers.contracts.update', $contract), [
                ...$payload,
                'title' => 'Updated cooperation metadata',
                'status' => 'active',
            ])->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('Updated cooperation metadata', $contract->fresh()->title);
        $this->assertSame('active', $contract->fresh()->status);

        $this->delete(route('admin.retailers.contracts.destroy', $contract))
            ->assertRedirect();

        $this->assertDatabaseMissing('retailer_contracts', ['id' => $contract->id]);
        $this->assertDatabaseHas('security_events', [
            'user_id' => $retailer->id,
            'event_type' => 'retailer_contract_deleted',
        ]);
    }

    public function test_contract_records_require_valid_dates_and_only_allow_approved_retailers(): void
    {
        $admin = $this->makeAdmin();
        [$pendingUser, $pendingProfile] = $this->makeRetailer('pending');
        [, $approvedProfile] = $this->makeRetailer();

        $payload = [
            'retailer_profile_id' => $approvedProfile->id,
            'reference' => 'BAD-DATES',
            'title' => 'Invalid date range',
            'contract_type' => 'Wholesale agreement',
            'effective_date' => '2026-12-31',
            'expiry_date' => '2026-01-01',
            'status' => 'draft',
        ];

        $this->actingAs($admin)
            ->from(route('admin.retailers.contracts.index'))
            ->post(route('admin.retailers.contracts.store'), $payload)
            ->assertRedirect(route('admin.retailers.contracts.index'))
            ->assertSessionHasErrors('expiry_date');

        $this->post(route('admin.retailers.contracts.store'), [
            ...$payload,
            'retailer_profile_id' => $pendingProfile->id,
            'reference' => 'PENDING-BLOCKED',
            'expiry_date' => '2027-01-01',
        ])->assertNotFound();

        $this->assertDatabaseCount('retailer_contracts', 0);
        $this->assertDatabaseHas('retailer_profiles', ['id' => $pendingProfile->id, 'user_id' => $pendingUser->id]);
    }
}
