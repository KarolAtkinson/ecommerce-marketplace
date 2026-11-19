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
                'short_description' => 'NATURADAŁA – naturalna forma bulionu kolagenowego, wygodna w codziennym stosowaniu.',
                'description' => <<<'DESCRIPTION'
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
DESCRIPTION,
            ]);
    }

    public function down(): void
    {
        DB::table('products')
            ->where('sku', 'NAT-BROTH-POULTRY-250')
            ->update([
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
DESCRIPTION,
            ]);
    }
};
