<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MarketplacePasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_request_and_complete_password_reset_with_mixed_case_email(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'customer@example.test',
            'remember_token' => 'old-remember-token',
        ]);
        $originalPasswordHash = $user->password;

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => '  CUSTOMER@Example.Test  '])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', 'password-link-requested');

        Notification::assertSentTo($user, ResetPassword::class);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => ' CUSTOMER@Example.Test ',
            'password' => 'new-reset-password-123',
            'password_confirmation' => 'new-reset-password-123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Hasło zostało zmienione. Zaloguj się ponownie.');

        $updatedUser = $user->fresh();
        $this->assertTrue(Hash::check('new-reset-password-123', $updatedUser->password));
        $this->assertNotSame($originalPasswordHash, $updatedUser->password);
        $this->assertNotSame('old-remember-token', $updatedUser->remember_token);
        $this->assertDatabaseHas('security_events', [
            'user_id' => $user->id,
            'event_type' => 'password_reset',
        ]);

        $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->post(route('password.store'), [
                'token' => $token,
                'email' => 'customer@example.test',
                'password' => 'another-reset-password-123',
                'password_confirmation' => 'another-reset-password-123',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('new-reset-password-123', $user->fresh()->password));
        $this->assertDatabaseCount('security_events', 1);

        $this->actingAs($updatedUser)
            ->withSession(['password_hash_web' => $originalPasswordHash])
            ->get(route('account.show'))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_password_reset_request_is_generic_for_inactive_accounts(): void
    {
        Notification::fake();
        $user = User::factory()->suspended()->create();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => '  '.mb_strtoupper($user->email).'  '])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', 'password-link-requested');

        Notification::assertNothingSent();
    }

    public function test_expired_or_invalid_reset_token_does_not_change_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.test',
            'remember_token' => 'old-remember-token',
        ]);
        $originalPasswordHash = $user->password;

        $this->from(route('password.reset', ['token' => 'invalid-reset-token', 'email' => $user->email]))
            ->post(route('password.store'), [
            'token' => 'invalid-reset-token',
            'email' => 'customer@example.test',
            'password' => 'new-reset-password-123',
            'password_confirmation' => 'new-reset-password-123',
        ])->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertSame($originalPasswordHash, $user->fresh()->password);
        $this->assertSame('old-remember-token', $user->fresh()->remember_token);
        $this->assertDatabaseCount('security_events', 0);
    }
}
