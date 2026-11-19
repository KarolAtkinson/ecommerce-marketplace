<?php

namespace Tests\Feature;

use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MarketplaceSecurityEventHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_customer_can_view_only_their_recent_security_events(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        SecurityEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'login_succeeded',
            'ip_address' => '192.0.2.10',
            'metadata' => ['secret' => 'must not be rendered'],
            'created_at' => now()->subHour(),
        ]);
        SecurityEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'password_changed',
            'created_at' => now(),
        ]);
        SecurityEvent::query()->create([
            'user_id' => $otherUser->id,
            'event_type' => 'login_failed',
            'created_at' => now()->addMinute(),
        ]);

        $this->actingAs($user)
            ->get(route('account.security-events.index'))
            ->assertOk()
            ->assertSee('Zmieniono hasło')
            ->assertSee('Udane logowanie')
            ->assertDontSee('192.0.2.10')
            ->assertDontSee('must not be rendered')
            ->assertDontSee('Nieudana próba logowania');
    }

    public function test_unverified_customer_cannot_view_security_event_history(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('account.security-events.index'))
            ->assertRedirect(route('verification.notice'));

        $this->post(route('account.security-events.revoke-other-sessions'), [
            'current_password' => 'password',
        ])->assertRedirect(route('verification.notice'));
    }

    public function test_customer_can_revoke_other_sessions_while_current_session_remains_active(): void
    {
        $user = User::factory()->create();
        $originalPasswordHash = $user->password;

        $this->actingAs($user)
            ->post(route('account.security-events.revoke-other-sessions'), [
                'current_password' => 'password',
            ])
            ->assertRedirect(route('account.security-events.index'))
            ->assertSessionHas('status', 'other-sessions-revoked');

        $updatedUser = $user->fresh();
        $this->assertNotSame($originalPasswordHash, $updatedUser->password);
        $this->assertAuthenticatedAs($updatedUser);
        $this->assertDatabaseHas('security_events', [
            'user_id' => $user->id,
            'event_type' => 'other_sessions_revoked',
        ]);

        $this->get(route('account.show'))->assertOk();

        $this->flushSession()
            ->actingAs($updatedUser)
            ->withSession(['password_hash_web' => $originalPasswordHash])
            ->get(route('account.show'))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_revoking_other_sessions_requires_the_current_password(): void
    {
        $user = User::factory()->create();
        $originalPasswordHash = $user->password;

        $this->actingAs($user)
            ->from(route('account.security-events.index'))
            ->post(route('account.security-events.revoke-other-sessions'), [
                'current_password' => 'incorrect-password',
            ])
            ->assertRedirect(route('account.security-events.index'))
            ->assertSessionHasErrors('current_password');

        $this->assertSame($originalPasswordHash, $user->fresh()->password);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertDatabaseCount('security_events', 0);
    }
}
