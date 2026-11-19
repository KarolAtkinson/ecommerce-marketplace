<?php

namespace Tests\Feature;

use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_selection_persists_across_storefront_and_auth_pages(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('name="locale" value="en"', false)
            ->assertSee('>EN</span>', false);

        $this->post(route('language.update'), ['locale' => 'en'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('locale', 'en');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('/en-translations.js?v=')
            ->assertSee('<h2 id="audience-title">Dla kogo</h2>', false)
            ->assertSee('name="locale" value="pl"', false)
            ->assertSee('>PL</span>', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('/en-translations.js?v=');

        $this->post(route('language.update'), ['locale' => 'pl'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('locale', 'pl');

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('<html lang="pl">', false)
            ->assertSee('>EN</span>', false);
    }

    public function test_only_supported_languages_can_be_selected(): void
    {
        $this->from(route('home'))
            ->post(route('language.update'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');
    }

    public function test_language_change_falls_back_to_home_when_no_page_referrer_is_available(): void
    {
        $this->from(route('language.update'))
            ->post(route('language.update'), ['locale' => 'en'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('locale', 'en');
    }

    public function test_product_details_use_locale_specific_expand_labels(): void
    {
        $this->seed(ProductSeeder::class);

        $this->withSession(['locale' => 'pl'])
            ->get(route('products.show', 'bulion-drobiowo-warzywny'))
            ->assertOk()
            ->assertSee('<html lang="pl">', false)
            ->assertSee('data-more-label="więcej"', false)
            ->assertSee('data-less-label="mniej"', false);

        $this->withSession(['locale' => 'en'])
            ->get(route('products.show', 'bulion-drobiowo-warzywny'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('data-more-label="more"', false)
            ->assertSee('data-less-label="less"', false);
    }
}
