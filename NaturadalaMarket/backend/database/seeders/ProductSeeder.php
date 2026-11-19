<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $collagenConcentrate = [
            'slug' => 'koncentrat-kolagenowy',
            'name' => 'Naturadała – suplement diety Bulion kolagenowy',
            'short_description' => 'Naturadała – suplement diety. Bulion kolagenowy w opakowaniu 400 ml, dwie porcje po 200 ml.',
            'description' => <<<'DESCRIPTION'
Bulion kolagenowy to naturalna baza.

Każdy z nas ma inny gust, smak i własne kulinarne upodobania. Dlatego bulion o naturalnym, delikatnym smaku, pozostawia przestrzeń na Twoje ulubione dodatki.

Dodaj ulubione zioła, przyprawy, odrobinę soli lub świeże warzywa. Przygotuj ją tak, jak lubisz – na ostro, aromatycznie, łagodnie lub klasycznie.

Nie narzucamy jednej kompozycji przypraw – to Ty decydujesz, jak będzie smakować Twój bulion.

Składniki:

Składniki: woda, szyja z indyka, łapki kurze, marchew, korzeń pietruszki, cebula, por, sól, ziele angielskie, pieprz młotkowany, liść laurowy.

Nie zawiera alergenów.

Instrukcja spożycia i przygotowania:

Produkt pasteryzowany, niedoprawiony do smaku.

Po otwarciu na produkcie znajduje się lekki osad. Obecność osadu jest zjawiskiem naturalnym wynikającym z tradycyjnego procesu długiego gotowania i nie wpływa na jakość produktu.

- Na zimno: spożywać w formie galarety, doprawić według uznania (np. sok z cytryny, świeże zioła, sól, pieprz).

- Na ciepło: podgrzać max do 70C (nie gotować), można dodawać do zup, sosów i marynat.

Warunki przechowywania:

Przechowywać w sposób niedostępny dla małych dzieci, w temperaturze do +4°C. Po otwarciu przechowywać w lodówce i spożyć w ciągu 72 godzin.

Ostrzeżenia:

Nie należy przekraczać zalecanej dziennej porcji do spożycia w ciągu dnia. Suplement diety nie może być stosowany jako zamiennik (zamiennik) zróżnicowanej diety. Zrównoważony sposób żywienia i prawidłowy tryb życia są ważne dla funkcjonowania organizmu człowieka.
DESCRIPTION,
            'size_label' => '400 ml · 2 porcje po 200 ml',
            'image_url' => '/images/products/naturadala-collagen-concentrate.png',
            'retail_price' => 34.99,
            'stock_quantity' => null,
            'minimum_order_quantity' => 1,
            'active' => true,
            'sort_order' => 1,
        ];

        $product = Product::query()->firstOrCreate(['sku' => 'NAT-COLLAGEN-320'], $collagenConcentrate);
        $product->forceFill([
            'slug' => $collagenConcentrate['slug'],
            'name' => $collagenConcentrate['name'],
            'short_description' => $collagenConcentrate['short_description'],
            'description' => $collagenConcentrate['description'],
            'size_label' => $collagenConcentrate['size_label'],
            'image_url' => $collagenConcentrate['image_url'],
            'retail_price' => $collagenConcentrate['retail_price'],
        ])->saveQuietly();

        $broths = [
            ['NAT-BROTH-BEEF-250', 'bulion-wolowo-cielecy', 'Bulion kolagenowy wołowo-cielęcy Naturadała', 'Bogaty smak i naturalny kolagen. Dzienna porcja 200 ml.', 'Wołowo-cielęcy bulion kolagenowy w formie galarety, suplement diety. Bogaty smak i naturalny kolagen: naturalny kolagen, wsparcie mięśni, zdrowe kości. Opakowanie 400 ml zawiera dwie dzienne porcje po 200 ml.', '/images/products/bulion-wolowo-cielecy.png', 2],
            ['NAT-BROTH-OMEGA3-250', 'bulion-omega-3', 'Bulion kolagenowy z Omega 3 Naturadała', 'Kolagen i Omega 3 w naturalnej formie. Dzienna porcja 200 ml.', 'Bulion kolagenowy z Omega 3 w formie galarety, suplement diety. Kolagen i Omega 3 w naturalnej formie: prawidłowa praca mózgu, zdrowe serce, wsparcie układu krążenia. Opakowanie 400 ml zawiera dwie dzienne porcje po 200 ml.', '/images/products/bulion-omega-3.png', 3],
            ['NAT-BROTH-POULTRY-250', 'bulion-drobiowo-warzywny', 'Bulion kolagenowy drobiowo-warzywny', 'NATURADAŁA – naturalna forma bulionu kolagenowego, wygodna w codziennym stosowaniu.', <<<'DESCRIPTION'
Produkt został przebadany laboratoryjnie pod kątem zawartości hydroksyproliny – aminokwasu charakterystycznego dla kolagenu.

Badania laboratoryjne

W dokumentacji wskazano normę PN-ISO 3496:2000, dotyczącą oznaczania zawartości hydroksyproliny, oraz rozporządzenie (UE) nr 1169/2011 jako podstawę zastosowanej metody obliczeniowej.

Zawartość kolagenu – wyniki i przeliczenia

Z przedstawionego wyniku laboratoryjnego wynika zawartość 4,16 g kolagenu w 100 g produktu (4,16%). Odpowiada to następującym ilościom w porcjach określonych wagowo:

Ilość produktu | Kolagen
100 g | 4,16 g
200 g | 8,32 g
250 g | 10,40 g
400 g | 16,64 g

Opakowanie: słoik o pojemności 400 ml. Zalecana dzienna porcja 200–250 ml.

Atuty produktu

· Polski produkt – obecny na rynku od około roku.
· Krajowe pochodzenie – wyprodukowany z wysokiej jakości, starannie wyselekcjonowanych surowców pochodzących z Polski.
· Gotowy do spożycia – bulion można spożywać bezpośrednio ze słoika, po odpowiednim podgrzaniu. Nie należy doprowadzać go do wrzenia.
· Wszechstronne zastosowanie – może być spożywany samodzielnie lub stanowić wartościowy element urozmaiconej, zbilansowanej diety.

Sposób przechowywania i ostrzeżenia

Przechowywać w sposób niedostępny dla małych dzieci, w temperaturze do +4°C. Po otwarciu przechowywać w lodówce i spożyć w ciągu 72 godzin.

Po otwarciu może pojawić się lekki osad. To naturalny efekt tradycyjnego, długiego gotowania, który nie wpływa na jakość produktu.

Nie należy przekraczać zalecanej dziennej porcji. Suplement diety nie może być stosowany jako zamiennik zróżnicowanej diety.
DESCRIPTION
                , '/images/products/bulion-drobiowo-warzywny.png', 4],
        ];

        foreach ($broths as [$sku, $slug, $name, $short, $description, $image, $order]) {
            $values = [
                'slug' => $slug,
                'name' => $name,
                'short_description' => $short,
                'description' => $description,
                'size_label' => '400 ml · 2 porcje po 200 ml',
                'image_url' => $image,
                'retail_price' => 34.99,
                'stock_quantity' => null,
                'minimum_order_quantity' => 1,
                'active' => true,
                'sort_order' => $order,
                'announced_at' => now(),
            ];

            $product = Product::query()->firstOrCreate(['sku' => $sku], $values);
            $product->forceFill([
                'slug' => $slug,
                'name' => $name,
                'short_description' => $short,
                'description' => $description,
                'size_label' => $values['size_label'],
                'retail_price' => $values['retail_price'],
            ])->saveQuietly();
        }
    }
}
