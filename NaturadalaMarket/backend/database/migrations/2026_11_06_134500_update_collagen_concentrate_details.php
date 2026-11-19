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
                'short_description' => 'Koncentrat kolagenowy w formie galarety. Opakowanie 400 ml zawiera dwie porcje po 200 ml.',
                'description' => <<<'DESCRIPTION'
Bulion kolagenowy to naturalna baza.

Każdy z nas ma inny gust, smak i własne kulinarne upodobania. Dlatego bulion o naturalnym, delikatnym smaku, pozostawia przestrzeń na Twoje ulubione dodatki.

Dodaj ulubione zioła, przyprawy, odrobinę soli lub świeże warzywa. Przygotuj ją tak, jak lubisz – na ostro, aromatycznie, łagodnie lub klasycznie.

Nie narzucamy jednej kompozycji przypraw – to Ty decydujesz, jak będzie smakować Twój bulion.

Warunki przechowywania:

Przechowywać w sposób niedostępny dla małych dzieci, w temperaturze do +4°C. Po otwarciu przechowywać w lodówce i spożyć w ciągu 72 godzin.

Ostrzeżenia:

Nie należy przekraczać zalecanej dziennej porcji do spożycia w ciągu dnia. Suplement diety nie może być stosowany jako zamiennik (zamiennik) zróżnicowanej diety. Zrównoważony sposób żywienia i prawidłowy tryb życia są ważne dla funkcjonowania organizmu człowieka.
DESCRIPTION,
                'size_label' => '400 ml · 2 porcje po 200 ml',
                'image_url' => '/images/products/naturadala-collagen-concentrate.png',
            ]);
    }

    public function down(): void
    {
        DB::table('products')
            ->where('sku', 'NAT-COLLAGEN-320')
            ->update([
                'short_description' => 'Koncentrat kolagenowy w formie żelu / galarety, przygotowywany w niskiej temperaturze.',
                'description' => 'Naturadała to wyjątkowy koncentrat kolagenowy w formie żelu / galarety. Produkt powstaje w procesie wolnego warzenia w niskiej temperaturze. Opakowanie zawiera dwie porcje dzienne; nie należy doprowadzać produktu do wrzenia.',
                'size_label' => '320 g · 2 porcje',
                'image_url' => '/images/naturadala-sloik.webp',
            ]);
    }
};
