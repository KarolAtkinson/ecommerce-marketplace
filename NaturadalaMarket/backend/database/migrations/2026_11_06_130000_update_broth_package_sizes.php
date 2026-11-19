<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $broths = [
            'NAT-BROTH-BEEF-250' => [
                'short_description' => 'Bogaty smak i naturalny kolagen. Dzienna porcja 200 ml.',
                'description' => 'Wołowo-cielęcy bulion kolagenowy w formie galarety, suplement diety. Bogaty smak i naturalny kolagen: naturalny kolagen, wsparcie mięśni, zdrowe kości. Opakowanie 400 ml zawiera dwie dzienne porcje po 200 ml.',
            ],
            'NAT-BROTH-OMEGA3-250' => [
                'short_description' => 'Kolagen i Omega 3 w naturalnej formie. Dzienna porcja 200 ml.',
                'description' => 'Bulion kolagenowy z Omega 3 w formie galarety, suplement diety. Kolagen i Omega 3 w naturalnej formie: prawidłowa praca mózgu, zdrowe serce, wsparcie układu krążenia. Opakowanie 400 ml zawiera dwie dzienne porcje po 200 ml.',
            ],
            'NAT-BROTH-POULTRY-250' => [
                'short_description' => 'Bulion kolagenowy drobiowo-warzywny, suplement diety. Zalecana dzienna porcja: 200 ml.',
                'description' => <<<'DESCRIPTION'
Instrukcja spożycia i przygotowania:

Zalecana dzienna porcja: 200 ml. Opakowanie 400 ml zawiera dwie dzienne porcje.

Produkt pasteryzowany, niedoprawiony do smaku. Obecność osadu jest zjawiskiem naturalnym wynikającym z tradycyjnego procesu długiego gotowania i nie wpływa na jakość produktu.

– Na zimno: spożywać w formie galarety, doprawić według uznania (np. sok z cytryny, świeże zioła, sól, pieprz).

– Na ciepło: podgrzać max do 70°C (nie gotować), można dodawać do zup, sosów i marynat.

Warunki przechowywania:

Przechowywać w sposób niedostępny dla małych dzieci, w temperaturze do +4°C. Po otwarciu przechowywać w lodówce i spożyć w ciągu 72 godzin.

Ostrzeżenia:

Nie należy przekraczać zalecanej dziennej porcji do spożycia w ciągu dnia. Suplement diety nie może być stosowany jako zamiennik (zamiennik) zróżnicowanej diety. Zrównoważony sposób żywienia i prawidłowy tryb życia są ważne dla funkcjonowania organizmu człowieka.
DESCRIPTION
                ,
            ],
        ];

        foreach ($broths as $sku => $details) {
            DB::table('products')
                ->where('sku', $sku)
                ->update(array_merge($details, ['size_label' => '400 ml · 2 porcje po 200 ml']));
        }
    }

    public function down(): void
    {
        $broths = [
            'NAT-BROTH-BEEF-250' => [
                'short_description' => 'Bogaty smak i naturalny kolagen. Wołowo-cielęcy bulion kolagenowy w formie galarety.',
                'description' => 'Wołowo-cielęcy bulion kolagenowy w formie galarety, suplement diety. Bogaty smak i naturalny kolagen: naturalny kolagen, wsparcie mięśni, zdrowe kości. Dostępne opakowania 250 ml i 400 ml.',
            ],
            'NAT-BROTH-OMEGA3-250' => [
                'short_description' => 'Kolagen i Omega 3 w naturalnej formie, w formie galarety.',
                'description' => 'Bulion kolagenowy z Omega 3 w formie galarety, suplement diety. Kolagen i Omega 3 w naturalnej formie: prawidłowa praca mózgu, zdrowe serce, wsparcie układu krążenia. Dostępne opakowania 250 ml i 400 ml.',
            ],
            'NAT-BROTH-POULTRY-250' => [
                'short_description' => 'Bulion kolagenowy drobiowo-warzywny, suplement diety.',
                'description' => <<<'DESCRIPTION'
Instrukcja spożycia i przygotowania:

Produkt pasteryzowany, niedoprawiony do smaku. Obecność osadu jest zjawiskiem naturalnym wynikającym z tradycyjnego procesu długiego gotowania i nie wpływa na jakość produktu.

– Na zimno: spożywać w formie galarety, doprawić według uznania (np. sok z cytryny, świeże zioła, sól, pieprz).

– Na ciepło: podgrzać max do 70°C (nie gotować), można dodawać do zup, sosów i marynat.

Warunki przechowywania:

Przechowywać w sposób niedostępny dla małych dzieci, w temperaturze do +4°C. Po otwarciu przechowywać w lodówce i spożyć w ciągu 72 godzin.

Ostrzeżenia:

Nie należy przekraczać zalecanej dziennej porcji do spożycia w ciągu dnia. Suplement diety nie może być stosowany jako zamiennik (zamiennik) zróżnicowanej diety. Zrównoważony sposób żywienia i prawidłowy tryb życia są ważne dla funkcjonowania organizmu człowieka.
DESCRIPTION
                ,
            ],
        ];

        foreach ($broths as $sku => $details) {
            DB::table('products')
                ->where('sku', $sku)
                ->update(array_merge($details, ['size_label' => '250 ml · galareta']));
        }
    }
};
