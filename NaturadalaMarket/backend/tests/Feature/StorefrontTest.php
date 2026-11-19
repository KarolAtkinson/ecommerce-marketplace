<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_renders_the_marketplace_storefront_and_assets(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('Oferta hurtowa | Naturadała')
            ->assertSee('Naturadała – suplement diety')
            ->assertSee('/styles.css')
            ->assertSee('/homepage-layout.css?v=')
            ->assertSee('/app.js?v=');
    }

    public function test_public_pages_use_the_shared_navigation_and_basket(): void
    {
        $this->seed(ProductSeeder::class);

        foreach (['/', route('products.index'), route('products.show', 'koncentrat-kolagenowy'), route('login')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('<header class="site-header">', false)
                ->assertSee('aria-controls="primary-navigation"', false)
                ->assertSee('<nav class="primary-navigation" id="primary-navigation"', false)
                ->assertSee('id="basket-popup"', false);
        }
    }

    public function test_homepage_shows_product_details_audience_and_qa_content(): void
    {
        $this->seed(ProductSeeder::class);

        $response = $this->get('/')
            ->assertOk()
            ->assertSee('Naturadała – suplement diety')
            ->assertSee('bulion-drobiowo-warzywny.png')
            ->assertSee('bulion-drobiowo-warzywny-detail.png')
            ->assertDontSee('bulion-drobiowo-warzywny-lemon.png')
            ->assertSee('naturadala-collagen-concentrate.png')
            ->assertSee('Naturadała – suplement diety. Bulion kolagenowy w opakowaniu 400 ml, dwie porcje po 200 ml.')
            ->assertSee('400 ml · 2 porcje po 200 ml')
            ->assertSee('Minimalna ilość')
            ->assertSee('1 szt.')
            ->assertDontSee('Warunki hurtowe')
            ->assertSee('Bulion kolagenowy drobiowo-warzywny')
            ->assertSee('Grzybowy')
            ->assertSee('Rybny')
            ->assertSee('alt="Bulion kolagenowy grzybowy Naturadała, opakowanie 400 ml"', false)
            ->assertSee('<span class="broth-bestseller" aria-label="Bestseller"><span>Bestseller</span></span>', false)
            ->assertSee('aria-label="Coming out Soon"', false)
            ->assertSee('<span>Coming out Soon</span>', false)
            ->assertSee('<h2 id="broth-title"><em>NATURADAŁA – kolagenowa inspiracja każdego dnia.</em></h2>', false)
            ->assertSee('/images/products/bulion-grzybowy-showcase.png')
            ->assertSee('/images/products/bulion-rybny-showcase.png')
            ->assertDontSee('NATURADAŁA – naturalna forma bulionu kolagenowego, wygodna w codziennym stosowaniu.')
            ->assertSee('<p class="nutrition-highlight">Zalecana dzienna porcja: 200–250 ml.</p>', false)
            ->assertDontSee('To NATURADAŁA – naturalna forma bulionu kolagenowego')
            ->assertSee('Bulion kolagenowy w postaci naturalnego żelu (galarety) - suplement diety.')
            ->assertSee('<a class="faq-reference" href="'.route('home').'#faq"><strong>Bulion kolagenowy w postaci naturalnego żelu (galarety) - suplement diety.</strong></a>', false)
            ->assertSee('Zalecana dzienna porcja: 200–250 ml.')
            ->assertSee('Produkt został przebadany laboratoryjnie pod kątem zawartości hydroksyproliny')
            ->assertSee('Na podstawie uzyskanych wyników badań określono zawartość kolagenu')
            ->assertSee('Badania laboratoryjne')
            ->assertSee('PN-ISO 3496:2000')
            ->assertSee('rozporządzenie (UE) nr 1169/2011')
            ->assertSee('Zawartość kolagenu – wyniki i przeliczenia')
            ->assertSee('4,16 g kolagenu w 100 g produktu (4,16%)')
            ->assertSee('<td>8,32 g</td>', false)
            ->assertSee('<td>10,40 g</td>', false)
            ->assertSee('<td>16,64 g</td>', false)
            ->assertSee('Gotowy do spożycia bezpośrednio z opakowania.')
            ->assertSee('Przeznaczony do stosowania w warunkach domowych.')
            ->assertSee('Atuty produktu')
            ->assertSee('Krajowe pochodzenie')
            ->assertSee('Nie należy doprowadzać go do wrzenia.')
            ->assertSee('Wartości odżywcze')
            ->assertSee('400 kJ / 96 kcal')
            ->assertSee('Suplement diety nie może być stosowany jako zamiennik zróżnicowanej diety.')
            ->assertSee('<a class="text-link" href="'.route('products.show', 'bulion-drobiowo-warzywny').'">Pozostałe informacje o produkcie, czytaj więcej…</a>', false)
            ->assertDontSee('w temperaturze do +4°C')
            ->assertDontSee('Nie należy przekraczać zalecanej dziennej porcji. Suplement diety nie może być stosowany jako zamiennik zróżnicowanej diety.')
            ->assertSee('Dla kogo')
            ->assertSee('<h2 id="audience-title">Dla kogo</h2>', false)
            ->assertSee('AKTYWNI')
            ->assertSee('SENIORZY')
            ->assertSee('KOBIETY NARAŻONE NA MENOPAUZĘ')
            ->assertSee('OSOBY W REHABILITACJI')
            ->assertSee('OSOBY PO ZABIEGACH')
            ->assertSee('ŚWIADOMI WYBORU')
            ->assertSee('Naturalna oferta')
            ->assertSee('Prosty proces')
            ->assertSee('Dobry dobór')
            ->assertSee('aria-label="Obróć kartę, aby zobaczyć zdjęcie"', false)
            ->assertSee('href="#dla-kogo"', false)
            ->assertSee('href="#faq"', false)
            ->assertSee('Dlaczego bulion kolagenowy')
            ->assertSee('Bulion kolagenowy to naturalna baza.')
            ->assertSee('Nie narzucamy jednej kompozycji przypraw');

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, 'class="nutrition-highlight hero-intro"'));
        $this->assertStringContainsString('<details class="hero-research">', $html);
        $this->assertStringContainsString('<details class="nutrition-disclosure">', $html);
        $nutritionPanel = explode('</details>', explode('<details class="nutrition-disclosure">', $html, 2)[1], 2)[0];
        $this->assertStringContainsString('Atuty produktu', $nutritionPanel);
        $this->assertStringContainsString('Krajowe pochodzenie', $nutritionPanel);
        $this->assertSame(2, substr_count($html, 'class="homepage-disclosure-summary"'));
        $this->assertStringContainsString('<p class="eyebrow"><span class="eyebrow-line"></span> Bulion kolagenowy drobiowo-warzywny</p>', $html);
        $this->assertStringContainsString('<h1>Suplement diety Naturadała</h1>', $html);
        $this->assertSame(9, substr_count($html, 'data-postcard-toggle'));
        $this->assertSame(9, substr_count($html, 'images/postcards/'));
        $this->assertSame(3, substr_count($html, 'class="broth-bestseller"'));
        $this->assertSame(2, substr_count($html, 'KOBIETY NARAŻONE NA MENOPAUZĘ'));
        $this->assertStringContainsString('aria-pressed="false"', $html);
        $this->assertSame(18, substr_count($html, '<span class="postcard-hint" aria-hidden="true">↻</span>'));
        $this->assertStringNotContainsString('Obróć kartę, aby zobaczyć zdjęcie</span>', $html);
        $this->assertStringNotContainsString('Obróć kartę, aby wrócić', $html);
        $imagePosition = strpos($html, 'class="hero-product"');
        $productInformationPosition = strpos($html, 'class="nutrition-facts"');

        $this->assertNotFalse($imagePosition);
        $this->assertNotFalse($productInformationPosition);
        $this->assertLessThan($productInformationPosition, $imagePosition);
        $this->assertLessThan(strpos($html, 'Atuty produktu'), strpos($html, 'Wartości odżywcze'));
    }

    public function test_postcard_images_are_available(): void
    {
        foreach ([
            'active',
            'senior',
            'women',
            'rehabilitation',
            'recovery',
            'conscious-choice',
            'natural-offer',
            'simple-process',
            'good-fit',
        ] as $image) {
            $this->assertFileExists(public_path("images/postcards/{$image}.jpg"));
        }
    }

    public function test_showcase_images_are_available(): void
    {
        $this->assertFileExists(public_path('images/products/bulion-grzybowy-showcase.png'));
        $this->assertFileExists(public_path('images/products/bulion-rybny-showcase.png'));
    }

    public function test_homepage_featured_product_uses_current_catalogue_values(): void
    {
        $this->seed(ProductSeeder::class);

        Product::query()
            ->where('slug', 'koncentrat-kolagenowy')
            ->update([
                'name' => 'Updated product name',
                'short_description' => 'Updated product description.',
                'size_label' => 'Updated size',
                'minimum_order_quantity' => 3,
                'image_url' => '/images/products/updated-product.png',
            ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Updated product name')
            ->assertSee('Updated product description.')
            ->assertSee('Updated size')
            ->assertSee('3 szt.')
            ->assertSee('/images/products/updated-product.png')
            ->assertDontSee('Naturadała – suplement diety Bulion kolagenowy');
    }

    public function test_broth_products_use_400_ml_packs_and_200_ml_daily_servings(): void
    {
        $this->seed(ProductSeeder::class);

        $broths = Product::query()
            ->whereIn('sku', ['NAT-BROTH-BEEF-250', 'NAT-BROTH-OMEGA3-250', 'NAT-BROTH-POULTRY-250'])
            ->orderBy('sort_order')
            ->get();

        $this->assertSame(3, $broths->count());
        $this->assertSame(
            ['400 ml · 2 porcje po 200 ml', '400 ml · 2 porcje po 200 ml', '400 ml · 2 porcje po 200 ml'],
            $broths->pluck('size_label')->all()
        );
        $this->assertTrue($broths->every(fn (Product $product): bool => str_contains($product->description, '200 ml') || str_contains($product->description, '200–250 ml')));
    }

    public function test_poultry_product_details_show_lab_results_and_storage_information(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get(route('products.show', 'bulion-drobiowo-warzywny'))
            ->assertOk()
            ->assertSee('Produkt został przebadany laboratoryjnie pod kątem zawartości hydroksyproliny')
            ->assertSee('<h2 class="product-description-heading">Badania laboratoryjne</h2>', false)
            ->assertSee('PN-ISO 3496:2000')
            ->assertSee('4,16 g kolagenu w 100 g produktu (4,16%)')
            ->assertSee('<th scope="col">Ilość produktu</th><th scope="col">Kolagen</th>', false)
            ->assertSee('<td>8,32 g</td>', false)
            ->assertSee('Atuty produktu')
            ->assertSee('Polski produkt – obecny na rynku od około roku.')
            ->assertSee('Sposób przechowywania i ostrzeżenia')
            ->assertSee('w temperaturze do +4°C')
            ->assertSee('Nie należy przekraczać zalecanej dziennej porcji.');
    }

    public function test_collagen_concentrate_uses_the_new_image_details_and_serving_size(): void
    {
        $this->seed(ProductSeeder::class);

        $product = Product::query()->where('sku', 'NAT-COLLAGEN-320')->firstOrFail();

        $this->assertSame('Naturadała – suplement diety Bulion kolagenowy', $product->name);
        $this->assertSame('400 ml · 2 porcje po 200 ml', $product->size_label);
        $this->assertSame('/images/products/naturadala-collagen-concentrate.png', $product->image_url);
        $this->assertStringContainsString('Bulion kolagenowy to naturalna baza.', $product->description);
        $this->assertStringContainsString('Składniki: woda, szyja z indyka, łapki kurze', $product->description);
        $this->assertStringContainsString('Nie zawiera alergenów.', $product->description);
        $this->assertStringNotContainsString('Opakowanie zawiera dwie porcje dzienne', $product->description);

        $response = $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('naturadala-collagen-concentrate.png')
            ->assertSee('<h1 class="product-detail-title">Bulion kolagenowy to naturalna baza.</h1>', false)
            ->assertSee('Bulion kolagenowy to naturalna baza.')
            ->assertSee('Każdy z nas ma inny gust, smak i własne kulinarne upodobania.')
            ->assertSee('Dodaj ulubione zioła, przyprawy, odrobinę soli lub świeże warzywa.')
            ->assertSee('Nie narzucamy jednej kompozycji przypraw')
            ->assertSee('<h2 class="product-description-heading">Składniki:</h2>', false)
            ->assertSee('Składniki: woda, szyja z indyka, łapki kurze')
            ->assertSee('Nie zawiera alergenów.')
            ->assertSee('<h2 class="product-description-heading">Instrukcja spożycia i przygotowania:</h2>', false)
            ->assertSee('<a class="faq-reference" href="'.route('home').'#faq"><strong>Produkt pasteryzowany, niedoprawiony do smaku.</strong></a>', false)
            ->assertSee('podgrzać max do 70C (nie gotować)')
            ->assertSee('<h2 class="product-description-heading">Warunki przechowywania:</h2>', false)
            ->assertSee('<h2 class="product-description-heading">Ostrzeżenia:</h2>', false)
            ->assertSee('400 ml · 2 porcje po 200 ml');

        $html = $response->getContent();
        $hiddenDetails = strpos($html, 'class="product-description-more"');
        $this->assertNotFalse($hiddenDetails);
        foreach ([
            'Bulion kolagenowy to naturalna baza.',
            'Każdy z nas ma inny gust, smak i własne kulinarne upodobania.',
            'Dodaj ulubione zioła, przyprawy, odrobinę soli lub świeże warzywa.',
            'Nie narzucamy jednej kompozycji przypraw',
        ] as $intro) {
            $position = strpos($html, $intro);
            $this->assertNotFalse($position);
            $this->assertLessThan($hiddenDetails, $position);
            $this->assertSame($intro === 'Bulion kolagenowy to naturalna baza.' ? 2 : 1, substr_count($html, $intro));
        }
        $this->assertStringContainsString('aria-expanded="false"', $html);
        $this->assertGreaterThan($hiddenDetails, strpos($html, '<h2 class="product-description-heading">Składniki:</h2>'));
        $this->assertGreaterThan($hiddenDetails, strpos($html, 'Po otwarciu na produkcie znajduje się lekki osad.'));
        $this->assertGreaterThan($hiddenDetails, strpos($html, '<h2 class="product-description-heading">Instrukcja spożycia i przygotowania:</h2>'));
    }

    public function test_all_seeded_products_are_priced_at_34_99_pln_and_product_detail_uses_the_updated_heading(): void
    {
        $this->seed(ProductSeeder::class);

        $products = Product::query()->orderBy('sort_order')->get();

        $this->assertSame(4, $products->count());
        $this->assertTrue($products->every(fn (Product $product): bool => (float) $product->retail_price === 34.99));

        $this->get(route('products.show', 'koncentrat-kolagenowy'))
            ->assertOk()
            ->assertSee('Produkt Naturadala')
            ->assertDontSee('NAT-COLLAGEN-320')
            ->assertSee('Naturadała – suplement diety Bulion kolagenowy')
            ->assertSee('<a class="faq-reference" href="'.route('home').'#faq"><strong>Produkt pasteryzowany, niedoprawiony do smaku.</strong></a>', false)
            ->assertSee('34,99 zł');
    }

    public function test_poultry_broth_details_are_shown_below_the_product_image(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get(route('products.show', 'bulion-drobiowo-warzywny'))
            ->assertOk()
            ->assertSee('Bulion kolagenowy drobiowo-warzywny')
            ->assertSee('/images/products/bulion-drobiowo-warzywny-detail.png')
            ->assertSee('>więcej</button>', false)
            ->assertSee('400 ml · 2 porcje po 200 ml')
            ->assertSee('Zawartość kolagenu – wyniki i przeliczenia')
            ->assertSee('Opakowanie: słoik o pojemności 400 ml. Zalecana dzienna porcja 200–250 ml.')
            ->assertSee('Sposób przechowywania i ostrzeżenia');
    }

    public function test_announcement_messages_come_from_config_sorted_alphabetically(): void
    {
        config()->set('announcements.messages', [
            ['text' => 'Zebra message'],
            ['text' => 'Łódź message'],
            ['text' => 'Alpha message'],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Alpha message', 'Łódź message', 'Zebra message'])
            ->assertSee('data-interval="20000"', false);
    }
}
