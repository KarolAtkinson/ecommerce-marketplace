<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class MarketplaceAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_stays_closed_until_marketplace_legal_documents_are_ready(): void
    {
        $this->get(route('legal.terms'))
            ->assertOk()
            ->assertSee('Regulamin')
            ->assertSee('REGULAMIN SKLEPU INTERNETOWEGO')
            ->assertSee('0001200631');

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('POLITYKA PRYWATNOŚCI SKLEPU')
            ->assertSee('5223352224');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Rejestracja ruszy po przygotowaniu')
            ->assertDontSee('name="password"');

        $this->post(route('register.store'), [])
            ->assertStatus(503);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_registered_customer_accepts_terms_and_verifies_email_before_account_access(): void
    {
        Notification::fake();
        $this->seed(RoleSeeder::class);
        config(['marketplace.registration_enabled' => true]);

        $response = $this->post(route('register.store'), [
            'name' => 'Test Customer',
            'email' => 'customer@example.test',
            'phone' => '123456789',
            'account_type' => 'customer',
            'terms_accepted' => '1',
            'marketing_consent' => '1',
            'password' => 'a-strong-test-password',
            'password_confirmation' => 'a-strong-test-password',
        ]);

        $user = User::query()->where('email', 'customer@example.test')->firstOrFail();

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $user->id,
            'role_id' => Role::query()->where('slug', 'customer')->value('id'),
        ]);
        $this->assertDatabaseHas('consent_records', [
            'user_id' => $user->id,
            'consent_type' => 'terms',
            'accepted' => 1,
        ]);
        $this->assertDatabaseHas('consent_records', [
            'user_id' => $user->id,
            'consent_type' => 'marketing',
            'accepted' => 1,
        ]);

        $this->get(route('account.show'))->assertRedirect(route('verification.notice'));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_retailer_applicant_has_no_wholesale_role_until_approved(): void
    {
        Notification::fake();
        $this->seed(RoleSeeder::class);
        config(['marketplace.registration_enabled' => true]);

        $this->post(route('register.store'), [
            'name' => 'Retail Applicant',
            'email' => 'retailer@example.test',
            'account_type' => 'retailer',
            'business_name' => 'Example Retail Company',
            'terms_accepted' => '1',
            'password' => 'a-strong-test-password',
            'password_confirmation' => 'a-strong-test-password',
        ])->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'retailer@example.test')->firstOrFail();

        $this->assertSame('pending', $user->retailerProfile->verification_status);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertFalse($user->hasRole('retailer'));
        $this->assertDatabaseCount('consent_records', 2);
    }

    public function test_valid_signed_email_verification_enables_the_account_area(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(10),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->get($verificationUrl)
            ->assertRedirect(route('account.show'))
            ->assertSessionHas('status', 'email-verified');

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('security_events', [
            'user_id' => $user->id,
            'event_type' => 'email_verified',
        ]);

        $this->get(route('account.show'))
            ->assertOk()
            ->assertSee($user->name);
    }

    public function test_verification_link_is_valid_for_five_minutes_only(): void
    {
        $user = User::factory()->unverified()->create();
        $mail = (new VerifyEmail())->toMail($user);
        $url = $mail->actionUrl;

        $this->travel(4)->minutes();
        $this->actingAs($user)->get($url)->assertRedirect(route('account.show'));
        $this->assertNotNull($user->fresh()->email_verified_at);

        $unverified = User::factory()->unverified()->create();
        $expiredUrl = (new VerifyEmail())->toMail($unverified)->actionUrl;
        $this->travel(6)->minutes();
        $this->actingAs($unverified)->get($expiredUrl)->assertForbidden();
        $this->assertNull($unverified->fresh()->email_verified_at);
    }

    public function test_suspended_accounts_cannot_log_in_or_reset_their_password(): void
    {
        $user = User::factory()->suspended()->create();
        $this->assertNotNull($user->suspended_at);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $response = $this->actingAs($user)->get(route('account.show'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_authenticator_setup_encrypts_the_secret_and_issues_single_use_recovery_codes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $setup = $this->get(route('mfa.show'))->assertOk();
        $secret = $setup->viewData('secret');
        $code = (new Google2FA)->getCurrentOtp($secret);

        $response = $this->post(route('mfa.store'), [
            'password' => 'password',
            'code' => $code,
        ])->assertRedirect(route('mfa.show'));

        $response->assertSessionHas('recoveryCodes');
        $this->assertCount(8, $response->getSession()->get('recoveryCodes'));

        $storedUser = $user->fresh();
        $this->assertSame($secret, $storedUser->two_factor_secret);
        $this->assertNotSame($secret, $storedUser->getRawOriginal('two_factor_secret'));
        $this->assertCount(8, $storedUser->two_factor_recovery_codes);
        $this->assertNotSame(
            $response->getSession()->get('recoveryCodes')[0],
            $storedUser->two_factor_recovery_codes[0]
        );
        $this->assertNotNull($storedUser->two_factor_confirmed_at);
    }

    public function test_authenticator_challenge_allows_login_once_and_rejects_a_replayed_code(): void
    {
        $google2fa = new Google2FA;
        $user = User::factory()->create();
        $secret = $google2fa->generateSecretKey();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.login'));
        $this->assertGuest();

        $code = $google2fa->getCurrentOtp($secret);
        $this->post(route('two-factor.login.store'), ['code' => $code])
            ->assertRedirect(route('account.show'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.login'));

        $this->post(route('two-factor.login.store'), ['code' => $code])
            ->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_a_recovery_code_can_only_be_used_once_to_complete_mfa_login(): void
    {
        $google2fa = new Google2FA;
        $user = User::factory()->create();
        $secret = $google2fa->generateSecretKey();
        $recoveryCode = 'A1B2C3D4-E5F6G7H8';
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => [Hash::make(str_replace('-', '', $recoveryCode))],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.login'));

        $this->post(route('two-factor.login.store'), ['code' => $recoveryCode])
            ->assertRedirect(route('account.show'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame([], $user->fresh()->two_factor_recovery_codes);
    }

    public function test_pending_mfa_challenge_is_invalidated_when_credentials_change(): void
    {
        $google2fa = new Google2FA;
        $user = User::factory()->create();
        $secret = $google2fa->generateSecretKey();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();
        $lastUsedStep = $user->two_factor_last_used_step;

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.login'));

        $this->assertSame(
            hash('sha256', $user->password."\0".$user->email),
            session('mfa.challenge.credential_fingerprint')
        );

        $user->forceFill([
            'password' => 'changed-password-after-mfa-start',
        ])->save();

        $this->post(route('two-factor.login.store'), [
            'code' => $google2fa->getCurrentOtp($secret),
        ])->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertFalse(session()->has('mfa.challenge'));
        $this->assertSame($lastUsedStep, $user->fresh()->two_factor_last_used_step);
    }

    public function test_pending_mfa_challenge_is_invalidated_when_email_changes(): void
    {
        $google2fa = new Google2FA;
        $user = User::factory()->create();
        $secret = $google2fa->generateSecretKey();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.login'));

        $user->forceFill(['email' => 'updated-'.$user->email])->save();

        $this->post(route('two-factor.login.store'), [
            'code' => $google2fa->getCurrentOtp($secret),
        ])->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertFalse(session()->has('mfa.challenge'));
        $this->assertNull($user->fresh()->two_factor_last_used_step);
    }
}
