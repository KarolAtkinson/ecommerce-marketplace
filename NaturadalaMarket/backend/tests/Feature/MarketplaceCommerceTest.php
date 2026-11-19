<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\RetailerProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\WholesalePrice;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceCommerceTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        return Product::query()->create([
            'sku' => 'TEST-PRODUCT-1',
            'slug' => 'test-product',
            'name' => 'Test product',
            'short_description' => 'Short product description.',
            'description' => 'Full product description.',
            'size_label' => 'Test size',
            'image_url' => null,
            'retail_price' => null,
            'stock_quantity' => null,
            'minimum_order_quantity' => 1,
            'active' => true,
            ...$overrides,
        ]);
    }

    private function makeApprovedRetailer(): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        RetailerProfile::query()->create([
            'user_id' => $user->id,
            'legal_business_name' => 'Verified Retail Company',
            'verification_status' => 'approved',
        ]);
        $user->roles()->attach(Role::query()->where('slug', 'retailer')->firstOrFail());

        return $user;
    }

    public function test_public_product_catalogue_never_exposes_wholesale_prices(): void
    {
        $product = $this->makeProduct();
        WholesalePrice::query()->create([
            'product_id' => $product->id,
            'unit_price' => '12.50',
            'minimum_quantity' => 10,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Test product')
            ->assertDontSee('12,50 zł');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Cena dostępna po kontakcie')
            ->assertDontSee('12,50 zł');
    }

    public function test_product_description_is_between_title_and_measurements_with_remainder_collapsed(): void
    {
        $product = $this->makeProduct([
            'description' => "First paragraph.\n\nAdditional product details.",
        ]);

        $response = $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Test product')
            ->assertSee('First paragraph.')
            ->assertSee('more')
            ->assertSee('Additional product details.')
            ->assertSee('data-product-description-toggle', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('hidden', false);

        $content = $response->getContent();
        $titlePosition = strpos($content, 'class="product-detail-title"');
        $descriptionPosition = strpos($content, 'First paragraph.');
        $measurementsPosition = strpos($content, 'Opakowanie');

        $this->assertIsInt($titlePosition);
        $this->assertIsInt($descriptionPosition);
        $this->assertIsInt($measurementsPosition);
        $this->assertLessThan($descriptionPosition, $titlePosition);
        $this->assertLessThan($measurementsPosition, $descriptionPosition);
    }

    public function test_legacy_single_paragraph_product_descriptions_keep_their_additional_sentences_expandable(): void
    {
        $product = $this->makeProduct([
            'description' => 'First sentence. Additional details remain available.',
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('First sentence.')
            ->assertSee('more')
            ->assertSee('Additional details remain available.')
            ->assertSee('data-product-description-toggle', false);
    }

    public function test_product_catalogue_paginates_and_preserves_search_and_sort_filters(): void
    {
        for ($number = 1; $number <= 13; $number++) {
            $this->makeProduct([
                'sku' => sprintf('CATALOGUE-%02d', $number),
                'slug' => sprintf('catalogue-product-%02d', $number),
                'name' => sprintf('Catalogue product %02d', $number),
            ]);
        }

        $this->get(route('products.index', ['q' => 'Catalogue', 'sort' => 'name']))
            ->assertOk()
            ->assertSee('Catalogue product 01')
            ->assertDontSee('Catalogue product 13')
            ->assertSee('page=2')
            ->assertSee('q=Catalogue');

        $this->get(route('products.index', ['q' => 'Catalogue', 'sort' => 'name', 'page' => 2]))
            ->assertOk()
            ->assertSee('Catalogue product 13')
            ->assertSee('q=Catalogue')
            ->assertSee('sort=name');

        $this->get(route('products.index', ['q' => 'no-matching-product']))
            ->assertOk()
            ->assertSee('Brak produktów pasujących do filtrów.')
            ->assertSee('Pokaż wszystkie produkty');
    }

    public function test_only_approved_verified_retailers_see_active_wholesale_prices(): void
    {
        $product = $this->makeProduct();
        WholesalePrice::query()->create([
            'product_id' => $product->id,
            'unit_price' => '12.50',
            'minimum_quantity' => 10,
        ]);

        $pendingRetailer = User::factory()->create();
        RetailerProfile::query()->create([
            'user_id' => $pendingRetailer->id,
            'legal_business_name' => 'Pending Retail Company',
            'verification_status' => 'pending',
        ]);

        $this->actingAs($pendingRetailer)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('12,50 zł');

        $approvedRetailer = $this->makeApprovedRetailer();
        $this->actingAs($approvedRetailer)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('12,50 zł')
            ->assertSee('netto');
    }

    public function test_wholesale_price_end_date_remains_active_through_the_selected_day(): void
    {
        $product = $this->makeProduct();
        WholesalePrice::query()->create([
            'product_id' => $product->id,
            'unit_price' => '12.50',
            'minimum_quantity' => 10,
            'starts_at' => '2026-10-12 00:00:00',
            'ends_at' => '2026-10-12 23:59:59',
        ]);
        $approvedRetailer = $this->makeApprovedRetailer();

        $this->travelTo(now()->parse('2026-10-12 23:59:59'));
        $this->actingAs($approvedRetailer)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('12,50 zł');

        $this->travelTo(now()->parse('2026-10-13 00:00:00'));
        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('12,50 zł');
    }

    public function test_inactive_products_are_not_available_in_catalogue_or_basket(): void
    {
        $product = $this->makeProduct(['active' => false]);
        $user = User::factory()->create();

        $this->get(route('products.index'))->assertDontSee('Test product');

        $this->actingAs($user)
            ->post(route('basket.store', $product), ['quantity' => 1])
            ->assertNotFound();

        $this->assertDatabaseCount('basket_items', 0);
    }

    public function test_stock_below_minimum_order_is_not_presented_as_available_or_addable(): void
    {
        $product = $this->makeProduct([
            'minimum_order_quantity' => 4,
            'stock_quantity' => 2,
        ]);
        $user = User::factory()->create();

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Brak dostępnej ilości minimalnej');

        $this->actingAs($user)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Brak dostępnej ilości minimalnej')
            ->assertSee('Obecny stan nie pozwala zrealizować minimalnej ilości zamówienia.')
            ->assertDontSee('Dodaj do koszyka');

        $this->post(route('basket.store', $product), ['quantity' => 4])
            ->assertSessionHasErrors('quantity');
    }

    public function test_basket_validates_minimum_quantity_and_ownership(): void
    {
        $product = $this->makeProduct([
            'minimum_order_quantity' => 3,
            'stock_quantity' => 5,
        ]);
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('basket.store', $product), ['quantity' => 2])
            ->assertSessionHasErrors('quantity');

        $this->post(route('basket.store', $product), ['quantity' => 3])
            ->assertRedirect(route('basket.show'));

        $item = $owner->fresh()->basket->items()->firstOrFail();

        $this->patch(route('basket.update', $item), ['quantity' => 6])
            ->assertSessionHasErrors('quantity');

        $this->actingAs($otherUser)
            ->patch(route('basket.update', $item), ['quantity' => 3])
            ->assertNotFound();

        $this->assertSame(3, $item->fresh()->quantity);
    }

    public function test_deactivated_basket_items_remain_visible_and_can_be_removed(): void
    {
        $product = $this->makeProduct(['active' => false]);
        $user = User::factory()->create();
        $basket = $user->basket()->create();
        $item = $basket->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('basket.show'))
            ->assertOk()
            ->assertSee('Test product')
            ->assertSee('Produkt nie jest obecnie dostępny w katalogu.')
            ->assertDontSee('Zmień ilość');

        $this->patch(route('basket.update', $item), ['quantity' => 3])
            ->assertNotFound();

        $this->assertSame(2, $item->fresh()->quantity);

        $this->delete(route('basket.destroy', $item))
            ->assertRedirect();

        $this->assertDatabaseMissing('basket_items', ['id' => $item->id]);
    }

    public function test_account_basket_can_prefill_a_database_backed_inquiry(): void
    {
        $product = $this->makeProduct(['name' => 'Basket inquiry product']);
        $inactiveProduct = $this->makeProduct([
            'sku' => 'TEST-INACTIVE-INQUIRY',
            'slug' => 'inactive-inquiry',
            'name' => 'Inactive basket product',
            'active' => false,
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('basket.store', $product), ['quantity' => 4])
            ->assertRedirect(route('basket.show'));
        $user->fresh()->basket->items()->create([
            'product_id' => $inactiveProduct->id,
            'quantity' => 2,
        ]);

        $this->post(route('basket.inquiry'))
            ->assertRedirect(route('contact'));

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Basket inquiry product')
            ->assertDontSee('Inactive basket product')
            ->assertSee('4')
            ->assertSee('value="'.e($user->name).'"', false)
            ->assertSee('value="'.e($user->email).'"', false);

        $this->post(route('contact.store'), [
            'name' => 'Basket Customer',
            'email' => 'basket@example.test',
            'products' => json_encode([
                ['name' => $product->name, 'quantity' => 4],
            ]),
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseHas('inquiries', [
            'email' => 'basket@example.test',
            'products' => json_encode([
                ['name' => $product->name, 'quantity' => 4],
            ]),
        ]);
    }

    public function test_wishlist_items_are_persisted_and_scoped_to_the_account(): void
    {
        $product = $this->makeProduct();
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('wishlist.store', $product))
            ->assertRedirect();

        $this->get(route('wishlist.index'))->assertOk()->assertSee('Test product');
        $this->actingAs($otherUser)->get(route('wishlist.index'))->assertOk()->assertDontSee('Test product');

        $this->actingAs($owner)
            ->delete(route('wishlist.destroy', $product))
            ->assertRedirect();

        $this->get(route('wishlist.index'))->assertDontSee('Test product');
    }

    public function test_wishlist_product_can_be_added_to_basket_at_its_minimum_order_quantity(): void
    {
        $product = $this->makeProduct([
            'minimum_order_quantity' => 3,
            'stock_quantity' => 5,
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('wishlist.store', $product))
            ->assertRedirect();

        $this->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('Dodaj do koszyka');

        $this->post(route('basket.store', $product), [
            'quantity' => $product->minimum_order_quantity,
        ])->assertRedirect(route('basket.show'));

        $this->assertDatabaseHas('basket_items', [
            'basket_id' => $user->fresh()->basket->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_wishlist_shows_unavailable_state_when_stock_is_below_minimum_order_quantity(): void
    {
        $product = $this->makeProduct([
            'minimum_order_quantity' => 4,
            'stock_quantity' => 2,
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('wishlist.store', $product))
            ->assertRedirect();

        $this->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('Chwilowo brak dostępnej ilości minimalnej.')
            ->assertDontSee('Dodaj do koszyka');
    }

    public function test_deactivated_wishlist_products_remain_visible_and_can_be_removed(): void
    {
        $product = $this->makeProduct(['active' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('wishlist.store', $product))
            ->assertNotFound();

        $wishlist = $user->wishlist()->create();
        $wishlist->items()->create(['product_id' => $product->id]);

        $this->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('Test product')
            ->assertSee('Produkt nie jest obecnie dostępny w katalogu.')
            ->assertDontSee('Dodaj do koszyka');

        $this->delete(route('wishlist.destroy', $product))
            ->assertRedirect();

        $this->assertDatabaseMissing('wishlist_items', [
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_retailer_application_is_pending_until_an_mfa_protected_admin_approves_it(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $applicant = User::factory()->create();

        $profile = RetailerProfile::query()->create([
            'user_id' => $applicant->id,
            'legal_business_name' => 'Applicant Company',
            'verification_status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.retailers.index'))
            ->assertForbidden();

        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->get(route('admin.retailers.index', ['q' => 'Applicant Company']))
            ->assertOk()
            ->assertSee('Applicant Company')
            ->assertSee($applicant->email);

        $this->actingAs($applicant)
            ->patch(route('admin.retailers.update', $profile), [
                'decision' => 'approve',
                'review_notes' => '',
                'filter_q' => 'Applicant Company',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('admin.retailers.update', $profile), [
                'decision' => 'approve',
                'review_notes' => 'Business details checked.',
                'filter_q' => 'Applicant Company',
            ])
            ->assertRedirect(route('admin.retailers.index', ['q' => 'Applicant Company']));

        $this->assertSame('approved', $profile->fresh()->verification_status);
        $this->assertNotNull($profile->fresh()->reviewed_by);
        $this->assertTrue($applicant->fresh()->hasRole('retailer'));
        $this->assertDatabaseHas('security_events', [
            'user_id' => $applicant->id,
            'event_type' => 'retailer_application_approved',
        ]);
    }

    public function test_verified_customer_can_apply_and_does_not_get_wholesale_permissions(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->post(route('retailer.application.store'), [
                'legal_business_name' => 'New Retail Applicant',
                'tax_identifier' => 'PL1234567890-RET-UPDATE-TEST',
                'business_phone' => '123456789',
                'website' => 'https://example.test',
            ])
            ->assertRedirect(route('account.show'));

        $profile = $customer->fresh()->retailerProfile;
        $this->assertSame('pending', $profile->verification_status);
        $this->assertFalse($customer->fresh()->hasRole('retailer'));

        $this->actingAs($customer)
            ->post(route('retailer.application.store'), [
                'legal_business_name' => 'Updated Retail Applicant',
                'tax_identifier' => 'PL1234567890-RET-UPDATE-TEST',
                'website' => 'https://example.test',
            ])
            ->assertRedirect(route('account.show'));

        $this->assertSame('Updated Retail Applicant', $profile->fresh()->legal_business_name);
    }

    public function test_admin_rejection_requires_a_reason_and_does_not_grant_retailer_role(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();
        $applicant = User::factory()->create();
        $profile = RetailerProfile::query()->create([
            'user_id' => $applicant->id,
            'legal_business_name' => 'Rejected Applicant',
            'verification_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.retailers.update', $profile), [
                'decision' => 'reject',
                'review_notes' => '',
            ])
            ->assertSessionHasErrors('review_notes');

        $this->assertSame('pending', $profile->fresh()->verification_status);
        $this->assertFalse($applicant->fresh()->hasRole('retailer'));

        $this->patch(route('admin.retailers.update', $profile), [
            'decision' => 'reject',
            'review_notes' => 'Business information requires further confirmation.',
        ])->assertRedirect(route('admin.retailers.index'));

        $this->assertSame('rejected', $profile->fresh()->verification_status);
        $this->assertFalse($applicant->fresh()->hasRole('retailer'));

        $this->actingAs($applicant)
            ->get(route('account.show'))
            ->assertOk()
            ->assertSee('Wymaga uzupełnienia')
            ->assertSee('Business information requires further confirmation.');

        $this->get(route('retailer.application'))
            ->assertOk()
            ->assertSee('Poprzedni wniosek')
            ->assertSee('Business information requires further confirmation.')
            ->assertSee('Wyślij wniosek ponownie');
    }
}
