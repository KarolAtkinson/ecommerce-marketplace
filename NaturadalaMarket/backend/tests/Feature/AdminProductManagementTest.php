<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_and_wholesale_pricing(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'sku' => 'ADM-TEST-001',
                'slug' => 'produkt-testowy',
                'name' => 'Produkt testowy',
                'short_description' => 'Krótki opis produktu.',
                'description' => 'Pełny opis produktu z ceną hurtową.',
                'size_label' => '250 g',
                'retail_price' => '39.90',
                'stock_quantity' => 42,
                'minimum_order_quantity' => 5,
                'active' => 1,
                'sort_order' => 2,
                'wholesale_prices' => [[
                    'minimum_quantity' => 10,
                    'unit_price' => '29.90',
                    'starts_at' => '',
                    'ends_at' => '',
                ]],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'produkt-testowy')->firstOrFail();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sku' => 'ADM-TEST-001',
            'name' => 'Produkt testowy',
        ]);
        $this->assertDatabaseHas('wholesale_prices', [
            'product_id' => $product->id,
            'minimum_quantity' => 10,
            'unit_price' => '29.90',
        ]);

        $product->update(['retail_price' => '0.00']);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('0,00 zł');
    }

    public function test_wholesale_tiers_must_meet_minimum_order_quantity_and_use_unique_thresholds(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $payload = [
            'sku' => 'ADM-TEST-TIERS',
            'slug' => 'produkt-progi-testowe',
            'name' => 'Produkt z progami testowymi',
            'short_description' => 'Krótki opis.',
            'description' => 'Pełny opis.',
            'minimum_order_quantity' => 5,
            'sort_order' => 1,
            'wholesale_prices' => [
                ['minimum_quantity' => 3, 'unit_price' => '20.00'],
            ],
        ];

        $this->actingAs($admin)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $payload)
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('wholesale_prices.0.minimum_quantity');

        $payload['wholesale_prices'] = [
            ['minimum_quantity' => 5, 'unit_price' => '20.00'],
            ['minimum_quantity' => 5, 'unit_price' => '18.00'],
        ];

        $this->post(route('admin.products.store'), $payload)
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('wholesale_prices.1.minimum_quantity');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('wholesale_prices', 0);
    }

    public function test_admin_can_search_filter_and_paginate_catalogue_products(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        foreach (range(1, 21) as $number) {
            Product::query()->create([
                'sku' => 'ADM-PAGE-'.$number,
                'slug' => 'admin-page-product-'.$number,
                'name' => 'Admin page product '.$number,
                'short_description' => 'Short description.',
                'description' => 'Product description.',
                'minimum_order_quantity' => 1,
                'active' => $number !== 21,
                'sort_order' => $number,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['q' => 'ADM-PAGE', 'active' => '0']))
            ->assertOk()
            ->assertSee('Admin page product 21')
            ->assertDontSee('Admin page product 1')
            ->assertSee('name="q" value="ADM-PAGE"', false)
            ->assertSee('name="active"', false)
            ->assertSee('1–1 z 1 produktów');

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['q' => 'Admin page product']))
            ->assertOk()
            ->assertSee('Wyświetlono 1–20 z 21 produktów')
            ->assertSee('page=2', false);
    }

    public function test_admin_can_add_and_remove_wholesale_price_tiers(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $product = Product::query()->create([
            'sku' => 'ADM-TIER-EDIT',
            'slug' => 'produkt-wiele-progow',
            'name' => 'Produkt wiele progów',
            'short_description' => 'Krótki opis.',
            'description' => 'Pełny opis.',
            'minimum_order_quantity' => 5,
            'active' => true,
            'sort_order' => 1,
        ]);
        $firstTier = $product->wholesalePrices()->create([
            'minimum_quantity' => 5,
            'unit_price' => '25.00',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('data-add-price-tier', false)
            ->assertSee('Dodaj próg cenowy')
            ->assertSee('Usuń próg');

        $this->put(route('admin.products.update', $product), [
            'sku' => $product->sku,
            'slug' => $product->slug,
            'name' => $product->name,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'minimum_order_quantity' => 5,
            'sort_order' => 1,
            'active' => 1,
            'wholesale_prices' => [
                ['id' => $firstTier->id, 'minimum_quantity' => 5, 'unit_price' => '25.00'],
                ['minimum_quantity' => 20, 'unit_price' => '20.00'],
            ],
        ])->assertRedirect(route('admin.products.index'));

        $secondTier = $product->wholesalePrices()->where('minimum_quantity', 20)->firstOrFail();
        $this->assertDatabaseCount('wholesale_prices', 2);

        $this->put(route('admin.products.update', $product), [
            'sku' => $product->sku,
            'slug' => $product->slug,
            'name' => $product->name,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'minimum_order_quantity' => 5,
            'sort_order' => 1,
            'active' => 1,
            'wholesale_prices' => [
                ['id' => $secondTier->id, 'minimum_quantity' => 20, 'unit_price' => '20.00'],
            ],
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseCount('wholesale_prices', 1);
        $this->assertDatabaseHas('wholesale_prices', [
            'id' => $secondTier->id,
            'product_id' => $product->id,
            'minimum_quantity' => 20,
        ]);
    }

    public function test_product_update_rejects_wholesale_tier_ids_from_another_product(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $productData = [
            'short_description' => 'Krótki opis.',
            'description' => 'Pełny opis.',
            'minimum_order_quantity' => 1,
            'active' => true,
            'sort_order' => 1,
        ];
        $product = Product::query()->create($productData + [
            'sku' => 'ADM-OWN-TIER',
            'slug' => 'produkt-wlasny-prog',
            'name' => 'Produkt własny',
        ]);
        $otherProduct = Product::query()->create($productData + [
            'sku' => 'ADM-OTHER-TIER',
            'slug' => 'produkt-obcy-prog',
            'name' => 'Inny produkt',
        ]);
        $foreignTier = $otherProduct->wholesalePrices()->create([
            'minimum_quantity' => 1,
            'unit_price' => '12.00',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), [
                'sku' => $product->sku,
                'slug' => $product->slug,
                'name' => $product->name,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'minimum_order_quantity' => 1,
                'sort_order' => 1,
                'active' => 1,
                'wholesale_prices' => [
                    ['id' => $foreignTier->id, 'minimum_quantity' => 1, 'unit_price' => '10.00'],
                ],
            ])
            ->assertRedirect(route('admin.products.edit', $product))
            ->assertSessionHasErrors('wholesale_prices.0.id');

        $this->assertDatabaseCount('wholesale_prices', 1);
        $this->assertDatabaseHas('wholesale_prices', [
            'id' => $foreignTier->id,
            'product_id' => $otherProduct->id,
            'unit_price' => '12.00',
        ]);
    }

    public function test_product_update_rejects_duplicate_wholesale_tier_ids(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $product = Product::query()->create([
            'sku' => 'ADM-DUPLICATE-ID',
            'slug' => 'produkt-powtorzone-id',
            'name' => 'Produkt powtórzone ID',
            'short_description' => 'Krótki opis.',
            'description' => 'Pełny opis.',
            'minimum_order_quantity' => 1,
            'active' => true,
            'sort_order' => 1,
        ]);
        $tier = $product->wholesalePrices()->create([
            'minimum_quantity' => 1,
            'unit_price' => '12.00',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), [
                'sku' => $product->sku,
                'slug' => $product->slug,
                'name' => $product->name,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'minimum_order_quantity' => 1,
                'sort_order' => 1,
                'active' => 1,
                'wholesale_prices' => [
                    ['id' => $tier->id, 'minimum_quantity' => 1, 'unit_price' => '11.00'],
                    ['id' => $tier->id, 'minimum_quantity' => 2, 'unit_price' => '10.00'],
                ],
            ])
            ->assertRedirect(route('admin.products.edit', $product))
            ->assertSessionHasErrors('wholesale_prices.1.id');

        $this->assertDatabaseCount('wholesale_prices', 1);
        $this->assertDatabaseHas('wholesale_prices', [
            'id' => $tier->id,
            'product_id' => $product->id,
            'minimum_quantity' => 1,
            'unit_price' => '12.00',
        ]);
    }

    public function test_admin_cannot_delete_a_product_saved_in_customer_baskets_or_wishlists(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $product = Product::query()->create([
            'sku' => 'ADM-SAVED-PRODUCT',
            'slug' => 'produkt-zapisany-klientom',
            'name' => 'Produkt zapisany klientom',
            'short_description' => 'Krótki opis.',
            'description' => 'Pełny opis.',
            'minimum_order_quantity' => 1,
            'active' => true,
            'sort_order' => 1,
        ]);

        $basketUser = User::factory()->create();
        $basket = $basketUser->basket()->create();
        $basketItem = $basket->items()->create([
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $wishlistUser = User::factory()->create();
        $wishlist = $wishlistUser->wishlist()->create();
        $wishlistItem = $wishlist->items()->create(['product_id' => $product->id]);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status', 'product-in-use');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('basket_items', ['id' => $basketItem->id, 'product_id' => $product->id]);
        $this->assertDatabaseHas('wishlist_items', ['id' => $wishlistItem->id, 'product_id' => $product->id]);

        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Zapisany przez klientów — dezaktywuj zamiast usuwać');

        $this->put(route('admin.products.update', $product), [
            'sku' => $product->sku,
            'slug' => $product->slug,
            'name' => $product->name,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'minimum_order_quantity' => 1,
            'sort_order' => 1,
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'active' => false]);
        $this->assertDatabaseHas('basket_items', ['id' => $basketItem->id, 'product_id' => $product->id]);
        $this->assertDatabaseHas('wishlist_items', ['id' => $wishlistItem->id, 'product_id' => $product->id]);
    }

    public function test_blank_wholesale_price_rows_do_not_block_product_save(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'sku' => 'ADM-OPTIONAL-TIER',
                'slug' => 'produkt-opcjonalne-ceny',
                'name' => 'Produkt opcjonalne ceny',
                'short_description' => 'Krótki opis.',
                'description' => 'Pełny opis.',
                'minimum_order_quantity' => 50,
                'sort_order' => 1,
                'wholesale_prices' => [
                    ['minimum_quantity' => 10, 'unit_price' => ''],
                    ['minimum_quantity' => 10, 'unit_price' => ''],
                ],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('sku', 'ADM-OPTIONAL-TIER')->firstOrFail();
        $this->assertSame(50, $product->minimum_order_quantity);
        $this->assertDatabaseCount('wholesale_prices', 0);
    }

    public function test_priced_wholesale_tier_requires_a_minimum_quantity(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'sku' => 'ADM-MISSING-TIER-MOQ',
                'slug' => 'produkt-brak-moq-progu',
                'name' => 'Produkt bez MOQ progu',
                'short_description' => 'Krótki opis.',
                'description' => 'Pełny opis.',
                'minimum_order_quantity' => 5,
                'sort_order' => 1,
                'wholesale_prices' => [
                    ['minimum_quantity' => '', 'unit_price' => '20.00'],
                ],
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('wholesale_prices.0.minimum_quantity');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('wholesale_prices', 0);
    }

    public function test_wholesale_tier_dates_cover_the_full_selected_calendar_days(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'sku' => 'ADM-TIER-DATES',
                'slug' => 'produkt-daty-progow',
                'name' => 'Produkt daty progów',
                'short_description' => 'Krótki opis.',
                'description' => 'Pełny opis.',
                'minimum_order_quantity' => 1,
                'sort_order' => 1,
                'wholesale_prices' => [[
                    'minimum_quantity' => 1,
                    'unit_price' => '20.00',
                    'starts_at' => '2026-10-12',
                    'ends_at' => '2026-10-12',
                ]],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('sku', 'ADM-TIER-DATES')->firstOrFail();
        $this->assertDatabaseHas('wholesale_prices', [
            'product_id' => $product->id,
            'starts_at' => '2026-10-12 00:00:00',
            'ends_at' => '2026-10-12 23:59:59',
        ]);
    }

    public function test_wholesale_tier_date_range_errors_are_shown_next_to_the_end_date(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'sku' => 'ADM-TIER-DATE-ERROR',
                'slug' => 'produkt-blad-daty-progu',
                'name' => 'Produkt błąd daty progu',
                'short_description' => 'Krótki opis.',
                'description' => 'Pełny opis.',
                'minimum_order_quantity' => 1,
                'sort_order' => 1,
                'wholesale_prices' => [[
                    'minimum_quantity' => 1,
                    'unit_price' => '20.00',
                    'starts_at' => '2026-10-15',
                    'ends_at' => '2026-10-14',
                ]],
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors([
                'wholesale_prices.0.ends_at' => 'Data końcowa musi być równa lub późniejsza niż data początkowa.',
            ]);

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('wholesale_prices', 0);
    }

    public function test_admin_can_delete_a_product_without_customer_references(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $admin->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $product = Product::query()->create([
            'sku' => 'ADM-UNUSED-PRODUCT',
            'slug' => 'produkt-bez-zapisow-klientow',
            'name' => 'Produkt bez zapisów klientów',
            'short_description' => 'Krótki opis.',
            'description' => 'Pełny opis.',
            'minimum_order_quantity' => 1,
            'active' => false,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status', 'product-deleted');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
