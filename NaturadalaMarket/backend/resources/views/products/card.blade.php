<article class="catalogue-card">
    <a class="catalogue-card-image" href="{{ route('products.show', $product) }}">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
        @else
            <span class="product-image-placeholder">Naturadała</span>
        @endif
        <span class="catalogue-card-link" aria-hidden="true">↗</span>
    </a>
    <div class="catalogue-card-copy">
        <p class="eyebrow">{{ $product->sku }}</p>
        <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
        <p>{{ $product->short_description }}</p>
        @if ($product->stock_quantity !== null)
            <p class="catalogue-availability {{ $product->stock_quantity >= $product->minimum_order_quantity ? 'is-available' : 'is-unavailable' }}">
                {{ $product->stock_quantity >= $product->minimum_order_quantity ? 'Dostępny' : 'Brak dostępnej ilości minimalnej' }}
            </p>
        @endif
        <div class="catalogue-price">
            @if ($approvedRetailer && $product->wholesalePrices->isNotEmpty())
                <span>Cena partnerska od</span>
                <strong>{{ number_format((float) $product->wholesalePrices->first()->unit_price, 2, ',', ' ') }} zł</strong>
                <small>netto · min. {{ $product->wholesalePrices->first()->minimum_quantity }} szt.</small>
            @elseif (! $approvedRetailer && $product->retail_price !== null)
                @if (auth()->user()?->isSubscriber())
                    <span>Cena dla subskrybenta (−{{ config('features.subscriber_discount_percent') }}%)</span>
                    <strong>{{ number_format($product->subscriberPrice(), 2, ',', ' ') }} zł <s class="price-was">{{ number_format((float) $product->retail_price, 2, ',', ' ') }} zł</s></strong>
                @else
                    <span>Cena detaliczna</span>
                    <strong>{{ number_format((float) $product->retail_price, 2, ',', ' ') }} zł</strong>
                    <small class="price-hint"><a href="#newsletter">Zapisz się do newslettera: −{{ config('features.subscriber_discount_percent') }}%</a></small>
                @endif
            @else
                <span>Warunki sprzedaży</span>
                <strong>Zapytaj o ofertę</strong>
            @endif
        </div>
        <div class="catalogue-card-actions">
            <a class="button button-dark" href="{{ route('products.show', $product) }}">Szczegóły produktu <span aria-hidden="true">↗</span></a>
            @auth
                @if (auth()->user()->hasVerifiedEmail())
                    <form method="POST" action="{{ $wishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}">
                        @csrf
                        @if ($wishlisted) @method('DELETE') @endif
                        <button class="icon-button" type="submit" aria-label="{{ $wishlisted ? 'Usuń z ulubionych' : 'Dodaj do ulubionych' }}">{{ $wishlisted ? '♥' : '♡' }}</button>
                    </form>
                @endif
            @endauth
        </div>
    </div>
</article>
