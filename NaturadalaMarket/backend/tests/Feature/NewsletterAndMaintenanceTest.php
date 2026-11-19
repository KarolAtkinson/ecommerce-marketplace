<?php

namespace Tests\Feature;

use App\Mail\NewProductAnnouncement;
use App\Mail\SubscriptionConfirmation;
use App\Models\Product;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewsletterAndMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'sku' => 'T-'.uniqid(),
            'name' => 'Test product',
            'slug' => 'test-'.uniqid(),
            'short_description' => 'x',
            'description' => 'x',
            'retail_price' => 21.99,
            'active' => true,
            'announced_at' => now(),
        ], $attrs));
    }

    public function test_subscribe_sends_confirmation_and_confirm_activates(): void
    {
        Mail::fake();

        $this->post('/newsletter', ['email' => 'A@Example.test', 'consent' => '1'])
            ->assertSessionHas('subscription_status', 'check-email');

        Mail::assertSent(SubscriptionConfirmation::class);
        $subscriber = Subscriber::firstOrFail();
        $this->assertNull($subscriber->confirmed_at);

        $this->get($subscriber->confirmUrl())->assertOk();
        $this->assertNotNull($subscriber->fresh()->confirmed_at);
        $this->assertTrue(Subscriber::active()->whereKey($subscriber->id)->exists());
    }

    public function test_subscribe_requires_consent_and_ignores_honeypot(): void
    {
        Mail::fake();

        $this->post('/newsletter', ['email' => 'a@example.test'])->assertSessionHasErrors('consent');
        $this->post('/newsletter', ['email' => 'b@example.test', 'consent' => '1', 'website' => 'spam']);

        $this->assertSame(0, Subscriber::count());
        Mail::assertNothingSent();
    }

    public function test_unsubscribe_deactivates(): void
    {
        $subscriber = Subscriber::create(['email' => 'c@example.test', 'consented_at' => now(), 'confirmed_at' => now()]);

        $this->get($subscriber->unsubscribeUrl())->assertOk();
        $this->post($subscriber->unsubscribeUrl())->assertOk();

        $this->assertNotNull($subscriber->fresh()->unsubscribed_at);
        $this->assertFalse(Subscriber::active()->exists());
    }

    public function test_new_active_product_is_announced_to_active_subscribers_once(): void
    {
        Mail::fake();
        Subscriber::create(['email' => 'd@example.test', 'consented_at' => now(), 'confirmed_at' => now()]);
        Subscriber::create(['email' => 'pending@example.test', 'consented_at' => now()]);

        $product = $this->product(['announced_at' => null]);
        $product->update(['name' => 'Renamed']);

        Mail::assertQueued(NewProductAnnouncement::class, 1);
        $this->assertNotNull($product->fresh()->announced_at);
    }

    public function test_subscriber_price_is_discounted(): void
    {
        $product = $this->product();
        $this->assertSame(19.79, $product->subscriberPrice());

        $user = User::factory()->create(['email' => 'e@example.test']);
        $this->assertSame(21.99, $product->priceFor($user));

        Subscriber::create(['email' => 'e@example.test', 'consented_at' => now(), 'confirmed_at' => now()]);
        $this->assertSame(19.79, $product->priceFor(User::find($user->id)));
    }

    public function test_retailer_area_is_closed_for_non_admins_during_maintenance(): void
    {
        config(['features.retailer_maintenance' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)->get('/retailer/workspace')->assertStatus(503);
    }
}
