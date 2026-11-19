<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('sku', 'NAT-COLLAGEN-320')
            ->update([
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
            ]);
    }

    public function down(): void
    {
        DB::table('products')
            ->where('sku', 'NAT-COLLAGEN-320')
            ->update([
                'description' => <<<'DESCRIPTION'
Każdy z nas ma inny gust, smak i własne kulinarne upodobania. Dlatego bulion o naturalnym, delikatnym smaku, pozostawia przestrzeń na Twoje ulubione dodatki.

Dodaj ulubione zioła, przyprawy, odrobinę soli lub świeże warzywa. Przygotuj ją tak, jak lubisz – na ostro, aromatycznie, łagodnie lub klasycznie.

Nie narzucamy jednej kompozycji przypraw – to Ty decydujesz, jak będzie smakować Twój bulion.

Składniki:

woda, szyja z indyka, łapki kurze, marchew, korzeń pietruszki, cebula, por, sól, ziele angielskie, pieprz młotkowany, liść laurowy.

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
            ]);
    }
};
