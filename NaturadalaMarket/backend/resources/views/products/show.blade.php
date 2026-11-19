@extends('products.layout')

@section('title', $product->name)
@section('description', $product->short_description)

@section('content')
    <section class="product-detail-section">
        <a class="text-link product-breadcrumb" href="{{ route('products.index') }}">← Wszystkie produkty</a>
        <article class="product-detail-card">
            <div class="product-image-wrap product-detail-image">
                @if ($product->image_url)
                    <img src="{{ $product->slug === 'bulion-drobiowo-warzywny' ? asset('images/products/bulion-drobiowo-warzywny-detail.png') : $product->image_url }}" alt="{{ $product->name }}">
                @else
                    <span class="product-image-placeholder">Naturadała</span>
                @endif
            </div>
            <div class="product-details">
                <p class="eyebrow">Produkt Naturadala</p>
                <h1 class="product-detail-title">{{ $product->sku === 'NAT-COLLAGEN-320' ? 'Bulion kolagenowy to naturalna baza.' : $product->name }}</h1>
                @foreach ($descriptionPreview as $previewParagraph)
                    <p class="product-description {{ $loop->last && count($descriptionRemainder) > 0 ? 'has-more' : '' }}">
                        @if (preg_match('/^(Składniki:)\s*(.*)$/us', $previewParagraph, $descriptionPreviewParts))
                            <strong>{{ $descriptionPreviewParts[1] }}</strong> <x-faq-linked-text :text="$descriptionPreviewParts[2]" />
                        @else
                            <x-faq-linked-text :text="$previewParagraph" />
                        @endif
                        @if ($loop->last && count($descriptionRemainder) > 0)
                            <button
                                class="product-description-toggle"
                                type="button"
                                aria-expanded="false"
                                aria-controls="product-description-more-{{ $product->id }}"
                                data-product-description-toggle
                                data-more-label="{{ app()->getLocale() === 'en' ? 'more' : 'więcej' }}"
                                data-less-label="{{ app()->getLocale() === 'en' ? 'less' : 'mniej' }}"
                            >{{ app()->getLocale() === 'en' ? 'more' : 'więcej' }}</button>
                        @endif
                    </p>
                @endforeach
                @if (count($descriptionRemainder) > 0)
                    <div class="product-description-more" id="product-description-more-{{ $product->id }}" hidden>
                        @foreach ($descriptionRemainder as $paragraph)
                            @php($descriptionLines = preg_split('/\R/u', trim($paragraph)) ?: [])
                            @if (in_array($paragraph, ['Badania laboratoryjne', 'Zawartość kolagenu – wyniki i przeliczenia', 'Atuty produktu', 'Sposób przechowywania i ostrzeżenia', 'Składniki:', 'Instrukcja spożycia i przygotowania:', 'Warunki przechowywania:', 'Ostrzeżenia:'], true))
                                <h2 class="product-description-heading">{{ $paragraph }}</h2>
                            @elseif (count($descriptionLines) > 1 && $descriptionLines[0] === 'Ilość produktu | Kolagen')
                                <table class="product-description-table">
                                    <thead>
                                        <tr><th scope="col">Ilość produktu</th><th scope="col">Kolagen</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach (array_slice($descriptionLines, 1) as $descriptionLine)
                                            @php($descriptionValues = explode(' | ', $descriptionLine, 2))
                                            @if (count($descriptionValues) === 2)
                                                <tr><th scope="row">{{ $descriptionValues[0] }}</th><td>{{ $descriptionValues[1] }}</td></tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            @elseif (count($descriptionLines) > 1 && collect($descriptionLines)->every(fn (string $line): bool => str_starts_with($line, '· ')))
                                <ul class="product-description-list">
                                    @foreach ($descriptionLines as $descriptionLine)
                                        <li>{{ preg_replace('/^·\s*/u', '', $descriptionLine) }}</li>
                                    @endforeach
                                </ul>
                            @elseif (preg_match('/^(-|–)\s*(Na zimno:|Na ciepło:)\s*(.*)$/u', $paragraph, $instructionParts))
                                <p><strong>{{ $instructionParts[1] }} {{ $instructionParts[2] }}</strong> <x-faq-linked-text :text="$instructionParts[3]" /></p>
                            @else
                                <p><x-faq-linked-text :text="$paragraph" /></p>
                            @endif
                        @endforeach
                    </div>
                @endif
                <ul class="product-facts">
                    @if ($product->size_label)
                        <li><span>Opakowanie</span><strong>{{ $product->size_label }}</strong></li>
                    @endif
                    <li><span>Forma</span><strong>Żel / galareta</strong></li>
                    <li><span>Minimalna ilość</span><strong>{{ $product->minimum_order_quantity }} szt.</strong></li>
                    @if ($product->stock_quantity !== null)
                        <li><span>Dostępność</span><strong>{{ $product->stock_quantity >= $product->minimum_order_quantity ? 'Dostępny' : 'Brak dostępnej ilości minimalnej' }}</strong></li>
                    @endif
                </ul>
                <div class="catalogue-price detail-price">
                    @if ($approvedRetailer && $product->wholesalePrices->isNotEmpty())
                        <span>Cena partnerska</span>
                        @foreach ($product->wholesalePrices as $price)
                            <strong>{{ number_format((float) $price->unit_price, 2, ',', ' ') }} zł <small>netto · od {{ $price->minimum_quantity }} szt.</small></strong>
                        @endforeach
                    @elseif (! $approvedRetailer && $product->retail_price !== null)
                        @if (auth()->user()?->isSubscriber())
                            <span>Cena dla subskrybenta (−{{ config('features.subscriber_discount_percent') }}%)</span>
                            <strong>{{ number_format($product->subscriberPrice(), 2, ',', ' ') }} zł <s class="price-was">{{ number_format((float) $product->retail_price, 2, ',', ' ') }} zł</s></strong>
                        @else
                            <span>Cena detaliczna</span>
                            <strong>{{ number_format((float) $product->retail_price, 2, ',', ' ') }} zł</strong>
                            <small class="price-hint"><a href="#newsletter">Zapisz się do newslettera: −{{ config('features.subscriber_discount_percent') }}% na wszystkie produkty</a></small>
                        @endif
                    @else
                        <span>Warunki sprzedaży</span>
                        <strong>Cena dostępna po kontakcie</strong>
                    @endif
                </div>
                <div class="product-detail-actions">
                    @auth
                        @if (auth()->user()->hasVerifiedEmail())
                            @if ($product->stock_quantity === null || $product->stock_quantity >= $product->minimum_order_quantity)
                                <form method="POST" action="{{ route('basket.store', $product) }}">
                                    @csrf
                                    <label class="quantity-label" for="quantity">Ilość</label>
                                    <input class="quantity-input" id="quantity" name="quantity" type="number" min="{{ $product->minimum_order_quantity }}" max="{{ min(999, $product->stock_quantity ?? 999) }}" value="{{ $product->minimum_order_quantity }}" required>
                                    <button class="button button-dark" type="submit">Dodaj do koszyka <span aria-hidden="true">+</span></button>
                                </form>
                            @else
                                <p class="form-hint">Obecny stan nie pozwala zrealizować minimalnej ilości zamówienia.</p>
                            @endif
                            @error('quantity') <span class="field-error">{{ $message }}</span> @enderror
                            <form method="POST" action="{{ $wishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}">
                                @csrf
                                @if ($wishlisted) @method('DELETE') @endif
                                <button class="button button-outline" type="submit">{{ $wishlisted ? 'Usuń z ulubionych' : 'Dodaj do ulubionych' }}</button>
                            </form>
                        @else
                            <p class="form-hint">Potwierdź adres e-mail, aby korzystać z koszyka i listy ulubionych.</p>
                        @endif
                    @else
                        <a class="button button-dark" href="{{ route('login') }}">Zaloguj się, aby dodać do koszyka <span aria-hidden="true">↗</span></a>
                    @endauth
                    @if (! $approvedRetailer && (! config('features.retailer_maintenance') || auth()->user()?->hasRole('admin')))
                        <a class="text-link" href="{{ auth()->check() && auth()->user()->hasVerifiedEmail() ? route('retailer.application') : route('register') }}">Zapytaj o współpracę hurtową <span aria-hidden="true">↗</span></a>
                    @endif
                </div>
                <p class="product-note">Informacje handlowe i aktualna dostępność wymagają potwierdzenia. Skład, porcjowanie i sposób użycia sprawdź na oficjalnym opakowaniu produktu.</p>
            </div>
        </article>
    </section>
@endsection
