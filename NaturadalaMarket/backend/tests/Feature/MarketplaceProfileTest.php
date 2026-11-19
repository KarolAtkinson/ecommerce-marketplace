<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MarketplaceProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_customer_can_update_name_and_phone_without_changing_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old-address@example.test',
            'phone' => '111111111',
        ]);

        $this->actingAs($user)
            ->get(route('account.profile.edit'))
            ->assertOk()
            ->assertSee('old-address@example.test');

        $this->put(route('account.profile.update'), [
            'name' => '  New Customer  ',
            'email' => 'attacker@example.test',
            'phone' => '  +48123456789  ',
        ])
            ->assertRedirect(route('account.show'))
            ->assertSessionHas('status', 'profile-updated');

        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'name' => 'New Customer',
            'email' => 'old-address@example.test',
            'phone' => '+48123456789',
        ]);
    }

    public function test_profile_update_can_clear_optional_phone_number(): void
    {
        $user = User::factory()->create(['phone' => '123456789']);

        $this->actingAs($user)
            ->put(route('account.profile.update'), [
                'name' => $user->name,
                'phone' => '   ',
            ])
            ->assertRedirect(route('account.show'));

        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'phone' => null,
        ]);
    }

    public function test_profile_name_is_required_and_unverified_customers_cannot_edit_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('account.profile.edit'))
            ->put(route('account.profile.update'), ['name' => '', 'phone' => '123'])
            ->assertRedirect(route('account.profile.edit'))
            ->assertSessionHasErrors('name');

        $unverifiedUser = User::factory()->unverified()->create();
        $this->actingAs($unverifiedUser)
            ->get(route('account.profile.edit'))
            ->assertRedirect(route('verification.notice'));

        $this->put(route('account.profile.update'), [
            'name' => 'Unauthorized update',
            'phone' => '123',
        ])->assertRedirect(route('verification.notice'));
        $this->put(route('account.profile.email.update'), [
            'email' => 'new-address@example.test',
            'current_password' => 'password',
        ])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'id' => $unverifiedUser->getKey(),
            'name' => $unverifiedUser->name,
            'email' => $unverifiedUser->email,
        ]);
    }

    public function test_verified_customer_can_change_email_after_password_confirmation_and_must_reverify(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'old-address@example.test']);
        $originalPasswordHash = $user->password;

        $this->actingAs($user)
            ->put(route('account.profile.email.update'), [
                'email' => '  NEW-ADDRESS@example.test ',
                'current_password' => 'password',
            ])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'email-change-verification-sent');

        $updatedUser = $user->fresh();
        $this->assertSame('new-address@example.test', $updatedUser->email);
        $this->assertNotSame($originalPasswordHash, $updatedUser->password);
        $this->assertNull($updatedUser->email_verified_at);
        $this->assertDatabaseHas('security_events', [
            'user_id' => $user->id,
            'event_type' => 'email_change_requested',
        ]);
        Notification::assertSentTo($updatedUser, VerifyEmail::class);

        $this->flushSession()
            ->actingAs($updatedUser)
            ->withSession(['password_hash_web' => $originalPasswordHash])
            ->get(route('account.show'))
            ->assertRedirect(route('login'));
        $this->assertGuest();

        $this->flushSession()->actingAs($updatedUser);
        $this->get(route('account.show'))
            ->assertRedirect(route('verification.notice'));

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(10),
            ['id' => $updatedUser->id, 'hash' => sha1($updatedUser->email)]
        );
        $this->get($verificationUrl)
            ->assertRedirect(route('account.show'))
            ->assertSessionHas('status', 'email-verified');

        $this->assertNotNull($updatedUser->fresh()->email_verified_at);
    }

    public function test_email_change_requires_the_current_password_and_a_unique_address(): void
    {
        $user = User::factory()->create(['email' => 'customer@example.test']);
        User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs($user)
            ->from(route('account.profile.edit'))
            ->put(route('account.profile.email.update'), [
                'email' => 'new-address@example.test',
                'current_password' => 'incorrect-password',
            ])
            ->assertRedirect(route('account.profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->put(route('account.profile.email.update'), [
            'email' => 'taken@example.test',
            'current_password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'customer@example.test',
        ]);
    }

    public function test_submitting_the_current_email_does_not_invalidate_verification(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'customer@example.test']);

        $this->actingAs($user)
            ->put(route('account.profile.email.update'), [
                'email' => 'CUSTOMER@example.test',
                'current_password' => 'password',
            ])
            ->assertRedirect(route('account.profile.edit'))
            ->assertSessionHas('status', 'email-unchanged');

        $this->assertNotNull($user->fresh()->email_verified_at);
        Notification::assertNothingSent();
    }
}
