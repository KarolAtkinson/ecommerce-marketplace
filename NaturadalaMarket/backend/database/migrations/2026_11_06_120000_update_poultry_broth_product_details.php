<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('sku', 'NAT-BROTH-POULTRY-250')
            ->update([
                'name' => 'Collagen Supplement',
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
            ]);
    }

    public function down(): void
    {
        DB::table('products')
            ->where('sku', 'NAT-BROTH-POULTRY-250')
            ->update([
                'name' => 'Bulion kolagenowy drobiowo-warzywny Naturadała',
                'short_description' => 'Naturalne wsparcie dla stawów, skóry i odporności, w formie galarety.',
                'description' => 'Drobiowo-warzywny bulion kolagenowy w formie galarety, suplement diety. Naturalne wsparcie dla stawów, skóry i odporności: wsparcie stawów, piękna skóra, silna odporność. Dostępne opakowania 250 ml i 400 ml.',
            ]);
    }
};
