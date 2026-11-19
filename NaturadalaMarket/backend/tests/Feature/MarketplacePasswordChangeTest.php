<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MarketplacePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create(['remember_token' => 'old-remember-token']);
        $this->actingAs($user);
        $originalPasswordHash = $user->password;

        $this->get(route('account.password.edit'))
            ->assertOk()
            ->assertSee('Obecne hasło');

        $this->put(route('account.password.update'), [
            'current_password' => 'password',
            'password' => 'new-unique-password-123',
            'password_confirmation' => 'new-unique-password-123',
        ])
            ->assertRedirect(route('account.show'))
            ->assertSessionHas('status', 'password-updated');

        $user = $user->fresh();
        $this->assertTrue(Hash::check('new-unique-password-123', $user->password));
        $this->assertNotSame($originalPasswordHash, $user->password);
        $this->assertNotSame('old-remember-token', $user->remember_token);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('security_events', [
            'user_id' => $user->getKey(),
            'event_type' => 'password_changed',
        ]);

        $this->flushSession()
            ->actingAs($user)
            ->withSession(['password_hash_web' => $originalPasswordHash])
            ->get(route('account.show'))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_password_change_rejects_wrong_current_or_reused_password_without_changes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->from(route('account.password.edit'))
            ->put(route('account.password.update'), [
                'current_password' => 'wrong-current-password',
                'password' => 'new-unique-password-123',
                'password_confirmation' => 'new-unique-password-123',
            ])
            ->assertRedirect(route('account.password.edit'))
            ->assertSessionHasErrors('current_password');

        $this->from(route('account.password.edit'))
            ->put(route('account.password.update'), [
                'current_password' => 'password',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('account.password.edit'))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertDatabaseCount('security_events', 0);
    }

    public function test_unverified_user_cannot_change_password_from_account_settings(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)
            ->get(route('account.password.edit'))
            ->assertRedirect(route('verification.notice'));

        $this->put(route('account.password.update'), [
            'current_password' => 'password',
            'password' => 'new-unique-password-123',
            'password_confirmation' => 'new-unique-password-123',
        ])->assertRedirect(route('verification.notice'));

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertDatabaseCount('security_events', 0);
    }
}
