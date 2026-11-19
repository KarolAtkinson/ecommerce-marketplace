@extends('auth.layout')

@section('title', 'Lista ulubionych')

@section('content')
    <h1 class="auth-title">Twoje<br><em>ulubione.</em></h1>
    <p class="auth-description">Zapisane produkty z katalogu Naturadała.</p>
    @error('quantity') <p class="field-error" role="alert">{{ $message }}</p> @enderror
    @if ($products->isEmpty())
        <div class="notice-panel">
            <strong>Lista jest pusta.</strong>
            <p>Przeglądaj produkty i dodawaj te, do których chcesz wrócić.</p>
            <a class="button button-dark" href="{{ route('products.index') }}">Przejdź do produktów <span aria-hidden="true">↗</span></a>
        </div>
    @else
        <div class="saved-product-list">
            @foreach ($products as $product)
                <article class="saved-product">
                    <a class="saved-product-image" href="{{ route('products.show', $product) }}">
                        @if ($product->image_url)<img src="{{ $product->image_url }}" alt="" loading="lazy">@endif
                    </a>
                    <div>
                        <h2>
                            @if ($product->active)
                                <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                            @else
                                {{ $product->name }}
                            @endif
                        </h2>
                        <p>{{ $product->short_description }}</p>
                        @if ($product->active)
                            <a class="text-link" href="{{ route('products.show', $product) }}">Szczegóły produktu <span aria-hidden="true">↗</span></a>
                        @else
                            <p class="basket-item-unavailable">Produkt nie jest obecnie dostępny w katalogu. Możesz usunąć go z listy.</p>
                        @endif
                        @if ($product->active && ($product->stock_quantity === null || $product->stock_quantity >= $product->minimum_order_quantity))
                            <form class="wishlist-basket-form" method="POST" action="{{ route('basket.store', $product) }}">
                                @csrf
                                <input type="hidden" name="quantity" value="{{ $product->minimum_order_quantity }}">
                                <button class="button button-dark button-small" type="submit">Dodaj do koszyka <span aria-hidden="true">+</span></button>
                            </form>
                        @elseif ($product->active)
                            <p class="field-hint">Chwilowo brak dostępnej ilości minimalnej.</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('wishlist.destroy', $product) }}">
                        @csrf
                        @method('DELETE')
                        <button class="remove-button" type="submit" aria-label="Usuń {{ $product->name }} z ulubionych">Usuń</button>
                    </form>
                </article>
            @endforeach
        </div>
    @endif
@endsection
