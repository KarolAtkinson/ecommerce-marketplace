<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class MarketplaceRecoveryCodeRotationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_rotate_recovery_codes_and_use_a_new_code_once(): void
    {
        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();
        $oldRecoveryCode = 'OLD-CODE-123456';
        $user = User::factory()->create([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_last_used_step' => $google2fa->getTimestamp() / 30 - 1,
            'two_factor_recovery_codes' => [Hash::make(str_replace('-', '', $oldRecoveryCode))],
        ]);

        $response = $this->actingAs($user)
            ->post(route('mfa.recovery-codes.regenerate'), [
                'current_password' => 'password',
                'code' => $google2fa->getCurrentOtp($secret),
            ])
            ->assertRedirect(route('mfa.show'))
            ->assertSessionHas('recoveryCodes');

        $recoveryCodes = $response->getSession()->get('recoveryCodes');
        $this->assertCount(8, $recoveryCodes);
        $updatedUser = $user->fresh();
        $this->assertCount(8, $updatedUser->two_factor_recovery_codes);
        $this->assertNotSame($secret, $updatedUser->getRawOriginal('two_factor_secret'));
        foreach ($recoveryCodes as $recoveryCode) {
            $this->assertTrue(collect($updatedUser->two_factor_recovery_codes)
                ->contains(fn (string $hashedCode): bool => Hash::check(str_replace('-', '', $recoveryCode), $hashedCode)));
        }
        $this->assertFalse(collect($updatedUser->two_factor_recovery_codes)
            ->contains(fn (string $hashedCode): bool => Hash::check(str_replace('-', '', $oldRecoveryCode), $hashedCode)));
        $this->assertDatabaseHas('security_events', [
            'user_id' => $user->id,
            'event_type' => 'mfa_recovery_codes_regenerated',
        ]);

        $this->get(route('mfa.show'))->assertSee($recoveryCodes[0]);
        $this->get(route('mfa.show'))->assertDontSee($recoveryCodes[0]);

        Auth::logout();
        $this->withSession([
            'mfa.challenge' => [
                'user_id' => $user->id,
                'credential_fingerprint' => hash('sha256', $updatedUser->password."\0".$updatedUser->email),
                'expires_at' => now()->addMinutes(5)->timestamp,
            ],
        ])->post(route('two-factor.login.store'), ['code' => $recoveryCodes[0]])
            ->assertRedirect(route('account.show'));

        $this->assertAuthenticatedAs($updatedUser);
        $this->assertCount(7, $updatedUser->fresh()->two_factor_recovery_codes);
    }

    public function test_recovery_code_rotation_requires_a_valid_password_and_fresh_authenticator_code(): void
    {
        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();
        $recoveryCodes = [Hash::make('OLDRECOVERYCODE')];
        $user = User::factory()->create([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_last_used_step' => $google2fa->getTimestamp() / 30,
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        $this->actingAs($user)
            ->from(route('mfa.show'))
            ->post(route('mfa.recovery-codes.regenerate'), [
                'current_password' => 'incorrect-password',
                'code' => $google2fa->getCurrentOtp($secret),
            ])
            ->assertRedirect(route('mfa.show'))
            ->assertSessionHasErrors('current_password');

        $this->post(route('mfa.recovery-codes.regenerate'), [
            'current_password' => 'password',
            'code' => '000000',
        ])
            ->assertRedirect(route('mfa.show'))
            ->assertSessionHasErrors('code');

        $this->assertSame($recoveryCodes, $user->fresh()->two_factor_recovery_codes);
        $this->assertDatabaseMissing('security_events', [
            'user_id' => $user->id,
            'event_type' => 'mfa_recovery_codes_regenerated',
        ]);
    }
}
