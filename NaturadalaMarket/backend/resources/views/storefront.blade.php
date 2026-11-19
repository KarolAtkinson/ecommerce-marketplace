<!doctype html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f6f0">
    <meta name="description" content="Poznaj bulion kolagenowy drobiowo-warzywny Naturadała: suplement diety w opakowaniu 400 ml, z zalecaną dzienną porcją 200 ml.">
    <title>Oferta hurtowa | Naturadała</title>
    <link rel="preconnect" href="https://naturadala.pl">
    <link rel="stylesheet" href="{{ asset('styles.css') }}?v={{ filemtime(public_path('styles.css')) }}">
    <link rel="stylesheet" href="{{ asset('homepage-layout.css') }}?v={{ filemtime(public_path('homepage-layout.css')) }}">
    <script src="{{ asset('image-fallback.js') }}?v={{ filemtime(public_path('image-fallback.js')) }}"></script>
    <script src="{{ asset('en-translations.js') }}?v={{ filemtime(public_path('en-translations.js')) }}" defer></script>
    <script src="{{ asset('cookie-consent.js') }}" defer></script>
    <script src="{{ asset('app.js') }}?v={{ filemtime(public_path('app.js')) }}" defer></script>
  </head>
  <body class="homepage">
    <a class="skip-link" href="#main">Przejdź do treści</a>

    @include('shared.announcement')

    @include('shared.header', ['currentPage' => 'home'])

    <main id="main">
      <section class="hero hero-nutrition" id="top">
        <div class="hero-copy hero-nutrition-copy">
          <p class="eyebrow"><span class="eyebrow-line"></span> Bulion kolagenowy drobiowo-warzywny</p>
          <h1>Suplement diety Naturadała</h1>
          <p class="nutrition-highlight hero-intro"><x-faq-linked-text text="Bulion kolagenowy w postaci naturalnego żelu (galarety) - suplement diety." /></p>
          <p class="nutrition-highlight">Zalecana dzienna porcja: 200–250 ml.</p>
          <div class="hero-actions">
            <a class="button button-dark" href="{{ route('products.show', 'bulion-drobiowo-warzywny') }}">Szczegóły produktu <span aria-hidden="true">↗</span></a>
            <a class="text-link" href="#dla-kogo">Dla kogo <span aria-hidden="true">↓</span></a>
          </div>
          <details class="hero-research">
          <summary class="homepage-disclosure-summary"><h2 class="nutrition-table-title">Badania laboratoryjne</h2><span class="homepage-disclosure-icon" aria-hidden="true"></span></summary>
          <div class="homepage-disclosure-content">
          <p class="nutrition-method">Produkt został przebadany laboratoryjnie pod kątem zawartości hydroksyproliny – aminokwasu charakterystycznego dla kolagenu. Na podstawie uzyskanych wyników badań określono zawartość kolagenu, zgodnie z dokumentacją badawczą.</p>
          <p class="nutrition-method">W dokumentacji wskazano normę PN-ISO 3496:2000, dotyczącą oznaczania zawartości hydroksyproliny, oraz rozporządzenie (UE) nr 1169/2011 jako podstawę zastosowanej metody obliczeniowej.</p>
          <ul class="product-benefits-list">
            <li>Produkt pasteryzowany.</li>
            <li>Gotowy do spożycia bezpośrednio z opakowania.</li>
            <li>Nie wymaga dodatkowej obróbki termicznej.</li>
            <li>Może być spożywany samodzielnie jako element urozmaiconej diety.</li>
            <li>Przeznaczony do stosowania w warunkach domowych.</li>
          </ul>
          <h2 class="nutrition-table-title">Zawartość kolagenu – wyniki i przeliczenia</h2>
          <p class="nutrition-method">Z przedstawionego wyniku laboratoryjnego wynika zawartość 4,16 g kolagenu w 100 g produktu (4,16%). Odpowiada to następującym ilościom w porcjach określonych wagowo:</p>
          <table class="collagen-table">
            <thead>
              <tr><th scope="col">Ilość produktu</th><th scope="col">Kolagen</th></tr>
            </thead>
            <tbody>
              <tr><th scope="row">100 g</th><td>4,16 g</td></tr>
              <tr><th scope="row">200 g</th><td>8,32 g</td></tr>
              <tr><th scope="row">250 g</th><td>10,40 g</td></tr>
              <tr><th scope="row">400 g</th><td>16,64 g</td></tr>
            </tbody>
          </table>
          <p class="nutrition-serving">Opakowanie: słoik o pojemności 400 ml. Zalecana dzienna porcja 200–250 ml.</p>
          </div>
          </details>
        </div>
        <div class="hero-nutrition-column">
          <div class="hero-art" aria-label="Bulion kolagenowy drobiowo-warzywny Naturadała">
            <div class="hero-art-note">Codzienna porcja<br>dobrego wyboru</div>
            <img class="hero-product" src="{{ asset('images/products/bulion-drobiowo-warzywny.png') }}" alt="Bulion kolagenowy drobiowo-warzywny Naturadała, opakowanie 400 ml">
            <span class="hero-spark hero-spark-one" aria-hidden="true">✳</span>
            <span class="hero-spark hero-spark-two" aria-hidden="true">✳</span>
            <span class="hero-caption">Naturadała · bulion kolagenowy</span>
          </div>
          <div class="nutrition-facts">
            <hr class="nutrition-divider">
            <details class="nutrition-disclosure">
            <summary class="homepage-disclosure-summary"><h2 class="nutrition-table-title">Wartości odżywcze</h2><span class="homepage-disclosure-icon" aria-hidden="true"></span></summary>
            <div class="homepage-disclosure-content">
            <table class="nutrition-table">
              <thead>
                <tr>
                  <th scope="col">Wartość odżywcza</th>
                  <th scope="col">w 100 g</th>
                  <th scope="col">w porcji 200 g (dzienna porcja)</th>
                </tr>
              </thead>
              <tbody>
                <tr><th scope="row">Energia</th><td>200 kJ / 48 kcal</td><td>400 kJ / 96 kcal</td></tr>
                <tr><th scope="row">Tłuszcz</th><td>2,7 g</td><td>5,4 g</td></tr>
                <tr><th scope="row">w tym kwasy tłuszczowe nasycone</th><td>0,6 g</td><td>1,2 g</td></tr>
                <tr><th scope="row">Węglowodany</th><td>&lt;0,5 g</td><td>&lt;1,0 g</td></tr>
                <tr><th scope="row">w tym cukry</th><td>&lt;0,5 g</td><td>&lt;1,0 g</td></tr>
                <tr><th scope="row">Białko</th><td>5,9 g</td><td>11,8 g</td></tr>
                <tr><th scope="row">Sól</th><td>0,77 g</td><td>1,54 g</td></tr>
              </tbody>
            </table>
            <p class="nutrition-serving">Suplement diety nie może być stosowany jako zamiennik zróżnicowanej diety.</p>
            <h2 class="nutrition-table-title nutrition-benefits-title">Atuty produktu</h2>
            <ul class="product-benefits-list">
              <li>Polski produkt – obecny na rynku od około roku.</li>
              <li>Krajowe pochodzenie – wyprodukowany z wysokiej jakości, starannie wyselekcjonowanych surowców pochodzących z Polski.</li>
              <li>Gotowy do spożycia – bulion można spożywać bezpośrednio ze słoika, po odpowiednim podgrzaniu. Nie należy doprowadzać go do wrzenia.</li>
              <li>Wszechstronne zastosowanie – może być spożywany samodzielnie lub stanowić wartościowy element urozmaiconej, zbilansowanej diety.</li>
            </ul>
            </div>
            </details>
            <a class="text-link" href="{{ route('products.show', 'bulion-drobiowo-warzywny') }}">Pozostałe informacje o produkcie, czytaj więcej…</a>
          </div>
        </div>
        <a class="hero-scroll" href="#dla-kogo" aria-label="Przewiń do informacji dla kogo jest produkt"><span></span> Dla kogo</a>
      </section>

      <section class="intro-band audience-section" id="dla-kogo" aria-labelledby="audience-title">
        <div class="intro-content">
          <h2 id="audience-title">Dla kogo</h2>
        </div>
        <div class="audience-grid">
          <article class="benefit-card postcard">
            <button class="postcard-toggle" type="button" aria-label="Obróć kartę, aby zobaczyć zdjęcie" aria-pressed="false" data-postcard-toggle>
              <span class="postcard-inner">
                <span class="postcard-face postcard-front">
                  <h3>AKTYWNI</h3>
                  <p>Dla osób aktywnych fizycznie, które dbają o zbilansowaną dietę i chcą wzbogacić ją w kolagen.</p>
                  <span class="postcard-hint" aria-hidden="true">Kliknij, aby zobaczyć zdjęcie <span aria-hidden="true">↻</span></span>
                </span>
                <span class="postcard-face postcard-back" aria-hidden="true">
                  <img src="{{ asset('images/postcards/active.jpg') }}" alt="" loading="lazy">
                  <span class="postcard-back-copy"><span class="postcard-back-title">AKTYWNI</span><span class="postcard-hint" aria-hidden="true">↻</span></span>
                </span>
              </span>
            </button>
          </article>
          <article class="benefit-card postcard">
            <button class="postcard-toggle" type="button" aria-label="Obróć kartę, aby zobaczyć zdjęcie" aria-pressed="false" data-postcard-toggle>
              <span class="postcard-inner">
                <span class="postcard-face postcard-front">
                  <h3>SENIORZY</h3>
                  <p>Dla osób dojrzałych, które stawiają na świadome odżywianie i chcą zadbać o jakość swojej codziennej diety.</p>
                  <span class="postcard-hint" aria-hidden="true">Kliknij, aby zobaczyć zdjęcie <span aria-hidden="true">↻</span></span>
                </span>
                <span class="postcard-face postcard-back" aria-hidden="true">
                  <img src="{{ asset('images/postcards/senior.jpg') }}" alt="" loading="lazy">
                  <span class="postcard-back-copy"><span class="postcard-back-title">SENIORZY</span><span class="postcard-hint" aria-hidden="true">↻</span></span>
                </span>
              </span>
            </button>
          </article>
          <article class="benefit-card postcard">
            <button class="postcard-toggle" type="button" aria-label="Obróć kartę, aby zobaczyć zdjęcie" aria-pressed="false" data-postcard-toggle>
              <span class="postcard-inner">
                <span class="postcard-face postcard-front">
                  <h3>KOBIETY NARAŻONE NA MENOPAUZĘ</h3>
                  <p>Dla kobiet, które chcą włączyć kolagen do codziennej rutyny żywieniowej, również w okresie menopauzy.</p>
                  <span class="postcard-hint" aria-hidden="true">Kliknij, aby zobaczyć zdjęcie <span aria-hidden="true">↻</span></span>
                </span>
                <span class="postcard-face postcard-back" aria-hidden="true">
                  <img src="{{ asset('images/postcards/women.jpg') }}" alt="" loading="lazy">
                  <span class="postcard-back-copy"><span class="postcard-back-title">KOBIETY NARAŻONE NA MENOPAUZĘ</span><span class="postcard-hint" aria-hidden="true">↻</span></span>
                </span>
              </span>
            </button>
          </article>
          <article class="benefit-card postcard">
            <button class="postcard-toggle" type="button" aria-label="Obróć kartę, aby zobaczyć zdjęcie" aria-pressed="false" data-postcard-toggle>
              <span class="postcard-inner">
                <span class="postcard-face postcard-front">
                  <h3>OSOBY W REHABILITACJI</h3>
                  <p>Dla osób, które w okresie powrotu do codziennej aktywności zwracają szczególną uwagę na odpowiednie odżywianie.</p>
                  <span class="postcard-hint" aria-hidden="true">Kliknij, aby zobaczyć zdjęcie <span aria-hidden="true">↻</span></span>
                </span>
                <span class="postcard-face postcard-back" aria-hidden="true">
                  <img src="{{ asset('images/postcards/rehabilitation.jpg') }}" alt="" loading="lazy">
                  <span class="postcard-back-copy"><span class="postcard-back-title">OSOBY W REHABILITACJI</span><span class="postcard-hint" aria-hidden="true">↻</span></span>
                </span>
              </span>
            </button>
          </article>
          <article class="benefit-card postcard">
            <button class="postcard-toggle" type="button" aria-label="Obróć kartę, aby zobaczyć zdjęcie" aria-pressed="false" data-postcard-toggle>
              <span class="postcard-inner">
                <span class="postcard-face postcard-front">
                  <h3>OSOBY PO ZABIEGACH</h3>
                  <p>Dla osób, które po zabiegach medycznych chcą świadomie komponować swoją dietę, zgodnie z zaleceniami specjalisty.</p>
                  <span class="postcard-hint" aria-hidden="true">Kliknij, aby zobaczyć zdjęcie <span aria-hidden="true">↻</span></span>
                </span>
                <span class="postcard-face postcard-back" aria-hidden="true">
                  <img src="{{ asset('images/postcards/recovery.jpg') }}" alt="" loading="lazy">
                  <span class="postcard-back-copy"><span class="postcard-back-title">OSOBY PO ZABIEGACH</span><span class="postcard-hint" aria-hidden="true">↻</span></span>
                </span>
              </span>
            </button>
          </article>
          <article class="benefit-card postcard">
            <button class="postcard-toggle" type="button" aria-label="Obróć kartę, aby zobaczyć zdjęcie" aria-pressed="false" data-postcard-toggle>
              <span class="postcard-inner">
                <span class="postcard-face postcard-front">
                  <h3>ŚWIADOMI WYBORU</h3>
                  <p>Dla wszystkich, którzy cenią jakość, prostotę i świadome wybory żywieniowe.</p>
                  <span class="postcard-hint" aria-hidden="true">Kliknij, aby zobaczyć zdjęcie <span aria-hidden="true">↻</span></span>
                </span>
                <span class="postcard-face postcard-back" aria-hidden="true">
                  <img src="{{ asset('images/postcards/conscious-choice.jpg') }}" alt="" loading="lazy">
                  <span class="postcard-back-copy"><span class="postcard-back-title">ŚWIADOMI WYBORU</span><span class="postcard-hint" aria-hidden="true">↻</span></span>
                </span>
              </span>
            </button>
          </article>
        </div>
      </section>

      <section class="benefits-section" aria-labelledby="partner-benefits-title">
        <div class="section-heading">
          <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Dla partnerów</p>
            <h2 id="partner-benefits-title">Współpraca, która<br><em>ma sens.</em></h2>
          </div>
          <p class="section-heading-note">Dzięki jasnym warunkom i prostemu procesowi łatwiej wprowadzić produkt do oferty i prowadzić sprzedaż z większym spokojem.</p>
        </div>

        <div class="benefits-grid">
          <article class="benefit-card postcard">
            <button class="postcard-toggle" type="button" aria-label="Obróć kartę, aby zobaczyć zdjęcie" aria-pressed="false" data-postcard-toggle>
              <span class="postcard-inner">
                <span class="postcard-face postcard-front">
                  <span class="benefit-number">01</span>
                  <h3>Naturalna oferta</h3>
                  <p>Produkt oparty na prostym składzie, z którego można budować spójny przekaz dla klientów i partnerów handlowych.</p>
                  <span class="postcard-hint" aria-hidden="true">↻</span>
                </span>
                <span class="postcard-face postcard-back" aria-hidden="true">
                  <img src="{{ asset('images/postcards/natural-offer.jpg') }}" alt="" loading="lazy">
                  <span class="postcard-back-copy"><span class="postcard-back-title">Naturalna oferta</span><span class="postcard-hint" aria-hidden="true">↻</span></span>
                </span>
              </span>
            </button>
          </article>
          <article class="benefit-card postcard">
            <button class="postcard-toggle" type="button" aria-label="Obróć kartę, aby zobaczyć zdjęcie" aria-pressed="false" data-postcard-toggle>
              <span class="postcard-inner">
                <span class="postcard-face postcard-front">
                  <span class="benefit-number">02</span>
                  <h3>Prosty proces</h3>
                  <p>Wystarczy kontakt, rozmowa i dopasowanie warunków, by rozpocząć współpracę bez zbędnych formalności.</p>
                  <span class="postcard-hint" aria-hidden="true">↻</span>
                </span>
                <span class="postcard-face postcard-back" aria-hidden="true">
                  <img src="{{ asset('images/postcards/simple-process.jpg') }}" alt="" loading="lazy">
                  <span class="postcard-back-copy"><span class="postcard-back-title">Prosty proces</span><span class="postcard-hint" aria-hidden="true">↻</span></span>
                </span>
              </span>
            </button>
          </article>
          <article class="benefit-card postcard">
            <button class="postcard-toggle" type="button" aria-label="Obróć kartę, aby zobaczyć zdjęcie" aria-pressed="false" data-postcard-toggle>
              <span class="postcard-inner">
                <span class="postcard-face postcard-front">
                  <span class="benefit-number">03</span>
                  <h3>Dobry dobór</h3>
                  <p>Produkt sprawdza się w punktach sprzedaży, gabinetach i kanałach online, gdzie liczy się jakość i wartość dla klienta.</p>
                  <span class="postcard-hint" aria-hidden="true">↻</span>
                </span>
                <span class="postcard-face postcard-back" aria-hidden="true">
                  <img src="{{ asset('images/postcards/good-fit.jpg') }}" alt="" loading="lazy">
                  <span class="postcard-back-copy"><span class="postcard-back-title">Dobry dobór</span><span class="postcard-hint" aria-hidden="true">↻</span></span>
                </span>
              </span>
            </button>
          </article>
        </div>
      </section>

      @if ($featuredProduct)
      <section class="product-section" id="oferta">
        <div class="section-heading">
          <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Poznaj produkt</p>
            <h2>Dobry początek<br><em>współpracy.</em></h2>
          </div>
          <p class="section-heading-note">Poznaj nasz produkt i zapytaj o warunki dopasowane do Twojego biznesu.</p>
        </div>

        <article class="product-card">
          <div class="product-image-wrap">
            <span class="product-stamp">Naturadała<br>od środka</span>
            <img src="{{ $featuredProduct->image_url }}" alt="{{ $featuredProduct->name }}">
          </div>
          <div class="product-details">
            <p class="eyebrow">Nasza propozycja</p>
            <h3>{{ $featuredProduct->name }}</h3>
            @if ($featuredProduct->short_description !== 'NATURADAŁA – naturalna forma bulionu kolagenowego, wygodna w codziennym stosowaniu.')
              <p class="product-description">{{ $featuredProduct->short_description }}</p>
            @endif
            <ul class="product-facts">
              @if ($featuredProduct->size_label)
              <li><span>Opakowanie</span><strong>{{ $featuredProduct->size_label }}</strong></li>
              @endif
              <li><span>Forma</span><strong>Żel / galareta</strong></li>
              <li><span>Minimalna ilość</span><strong>{{ $featuredProduct->minimum_order_quantity }} szt.</strong></li>
              @if ($featuredProduct->stock_quantity !== null)
              <li><span>Dostępność</span><strong>{{ $featuredProduct->stock_quantity >= $featuredProduct->minimum_order_quantity ? 'Dostępny' : 'Brak dostępnej ilości minimalnej' }}</strong></li>
              @endif
            </ul>
            <div class="product-actions">
              <a class="button button-dark" href="{{ route('products.show', $featuredProduct) }}">Szczegóły produktu <span aria-hidden="true">↗</span></a>
              <button class="button button-dark add-to-cart" type="button" data-product="{{ $featuredProduct->name }} · {{ $featuredProduct->size_label }}">
                Dodaj do zapytania <span aria-hidden="true">+</span>
              </button>
              <a class="text-link" href="https://naturadala.pl/" target="_blank" rel="noreferrer">Informacje o produkcie <span aria-hidden="true">↗</span></a>
            </div>
            <p class="product-note">Informacje handlowe i aktualna dostępność wymagają potwierdzenia. Skład, porcjowanie i sposób użycia sprawdź na oficjalnym opakowaniu produktu.</p>
          </div>
        </article>
      </section>
      @endif

      <section class="broth-showcase" id="bulion" aria-labelledby="broth-title">
        <div class="section-heading">
          <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Nowa linia</p>
            <h2 id="broth-title"><em>NATURADAŁA – kolagenowa inspiracja każdego dnia.</em></h2>
          </div>
          <a class="button button-outline" href="{{ route('products.index') }}">Zobacz całą ofertę <span aria-hidden="true">↗</span></a>
        </div>
        <div class="broth-grid">
          <a class="broth-card broth-beef" href="{{ route('products.show', 'bulion-wolowo-cielecy') }}">
            <img src="{{ asset('images/products/bulion-grzybowy-showcase.png') }}" alt="Bulion kolagenowy grzybowy Naturadała, opakowanie 400 ml" loading="lazy">
            <span class="broth-bestseller" aria-label="Coming out Soon"><span>Coming out Soon</span></span>
            <span class="broth-label">Grzybowy <span aria-hidden="true">↗</span></span>
          </a>
          <a class="broth-card broth-omega" href="{{ route('products.show', 'bulion-omega-3') }}">
            <img src="{{ asset('images/products/bulion-rybny-showcase.png') }}" alt="Bulion kolagenowy rybny Naturadała, opakowanie 400 ml" loading="lazy">
            <span class="broth-bestseller" aria-label="Coming out Soon"><span>Coming out Soon</span></span>
            <span class="broth-label">Rybny <span aria-hidden="true">↗</span></span>
          </a>
          <a class="broth-card broth-poultry" href="{{ route('products.show', 'bulion-drobiowo-warzywny') }}">
            <img src="{{ asset('images/products/bulion-drobiowo-warzywny-detail.png') }}" alt="Bulion kolagenowy drobiowo-warzywny Naturadała, opakowanie 400 ml" loading="lazy">
            <span class="broth-bestseller" aria-label="Bestseller"><span>Bestseller</span></span>
            <span class="broth-label">Drobiowo-warzywny <span aria-hidden="true">↗</span></span>
          </a>
        </div>
      </section>

      <section class="story-section" id="o-nas">
        <div class="story-image">
          <img src="{{ asset('images/honorata-naturadala.jpg') }}" alt="Przedstawicielka marki Naturadała">
          <span class="image-caption">Tworzymy z myślą o naturze i codzienności.</span>
        </div>
        <div class="story-copy">
          <p class="eyebrow"><span class="eyebrow-line"></span> O Naturadale</p>
          <h2>Codzienne wsparcie,<br><em>tak jak natura dała.</em></h2>
          <p>Naturadała powstała z potrzeby połączenia nowoczesnej suplementacji z poszanowaniem naturalnego rytmu organizmu. Wierzymy w uważne podejście, prostotę i rozwiązania, które dobrze wpisują się w codzienne życie.</p>
          <a class="text-link" href="https://naturadala.pl/" target="_blank" rel="noreferrer">Odwiedź stronę Naturadała <span aria-hidden="true">↗</span></a>
        </div>
      </section>

      <section class="steps-section">
        <div class="section-heading">
          <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Prosto i po partnersku</p>
            <h2>Jak zacząć<br><em>współpracę?</em></h2>
          </div>
          <a class="button button-outline" href="#kontakt">Porozmawiajmy <span aria-hidden="true">↗</span></a>
        </div>
        <div class="steps-grid">
          <article class="step-card">
            <span>01</span>
            <h3>Opowiedz nam o swoim miejscu</h3>
            <p>Napisz, czym zajmuje się Twoja firma i jakiej współpracy szukasz.</p>
          </article>
          <article class="step-card">
            <span>02</span>
            <h3>Porozmawiajmy o możliwościach</h3>
            <p>Wspólnie omówimy produkt, dostępność i warunki partnerskie.</p>
          </article>
          <article class="step-card">
            <span>03</span>
            <h3>Dobierzmy dobry początek</h3>
            <p>Ustalimy szczegóły zamówienia i sposób dostawy.</p>
          </article>
        </div>
      </section>

      <section class="faq-section" id="faq" aria-labelledby="faq-title">
        <div class="section-heading">
          <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Pytania i odpowiedzi</p>
            <h2 id="faq-title">Dlaczego bulion kolagenowy<br><em>jest niedoprawiony?</em></h2>
          </div>
        </div>
        <div class="faq-list">
          <article class="faq-item is-open">
            <div class="faq-answer-content">
              <p><strong>Bulion kolagenowy to naturalna baza.</strong></p>
              <p>Każdy z nas ma inny gust, smak i własne kulinarne upodobania. Dlatego bulion o naturalnym, delikatnym smaku, pozostawia przestrzeń na Twoje ulubione dodatki.</p>
              <p>Dodaj ulubione zioła, przyprawy, odrobinę soli lub świeże warzywa. Przygotuj ją tak, jak lubisz – na ostro, aromatycznie, łagodnie lub klasycznie.</p>
              <p>Nie narzucamy jednej kompozycji przypraw – to Ty decydujesz, jak będzie smakować Twój bulion.</p>
            </div>
          </article>
        </div>
      </section>

      <section class="contact-section" id="kontakt">
        <div class="contact-mark" aria-hidden="true">✳</div>
        <p class="eyebrow">Porozmawiajmy</p>
        <h2>Współpraca zaczyna się<br>od <em>dobrego kontaktu.</em></h2>
        <p>Chętnie odpowiemy na pytania o produkt i możliwości współpracy.</p>
        <a class="button button-light" href="{{ route('contact') }}">Wyślij zapytanie <span aria-hidden="true">↗</span></a>
        <a class="contact-email" href="mailto:kontakt@naturadala.pl">kontakt@naturadala.pl</a>
      </section>
    </main>

    @include('shared.newsletter')
    <footer class="site-footer">
      <a class="footer-brand" href="{{ route('home') }}" aria-label="Wróć na górę">
        <img src="{{ asset('images/logo-footer.png') }}" alt="Naturadała">
      </a>
      <p>Codzienne wsparcie organizmu<br>tak jak natura dała.</p>
      <nav aria-label="Nawigacja w stopce">
        <a href="{{ route('products.index') }}">Produkty</a>
        <a href="#dla-kogo">Dla kogo</a>
        <a href="#faq">Pytania i odpowiedzi</a>
        <a href="#o-nas">O nas</a>
        <a href="#kontakt">Kontakt</a>
      </nav>
      <span class="copyright">© <span id="current-year">2026</span> Naturadała <button class="cookie-settings-link" type="button" data-cookie-settings>Ustawienia cookies</button></span>
    </footer>
    @include('shared.cookie-consent')

    @include('shared.basket-panel')
  </body>
</html>
