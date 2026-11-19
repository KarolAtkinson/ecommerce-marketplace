@extends('auth.layout')

@section('title', 'Koszyk')

@section('content')
    <h1 class="auth-title">Twój<br><em>koszyk.</em></h1>
    @if (session('status') === 'basket-item-added')
        <p class="status-message">Produkt dodano do koszyka.</p>
    @elseif (session('status') === 'basket-updated')
        <p class="status-message">Ilość produktu została zaktualizowana.</p>
    @elseif (session('status') === 'basket-item-removed')
        <p class="status-message">Produkt został usunięty z koszyka.</p>
    @endif
    @if ($errors->has('basket'))
        <p class="field-error" role="alert">{{ $errors->first('basket') }}</p>
    @endif
    <p class="auth-description">Zawartość koszyka jest zapisana na Twoim koncie. Ceny oraz dostępność wymagają potwierdzenia.</p>
    @if ($items->isEmpty())
        <div class="notice-panel">
            <strong>Koszyk jest pusty.</strong>
            <p>Wróć do katalogu, aby wybrać produkt.</p>
            <a class="button button-dark" href="{{ route('products.index') }}">Przejdź do produktów <span aria-hidden="true">↗</span></a>
        </div>
    @else
        <div class="saved-product-list">
            @foreach ($items as $item)
                <article class="saved-product">
                    <a class="saved-product-image" href="{{ route('products.show', $item->product) }}">
                        @if ($item->product->image_url)<img src="{{ $item->product->image_url }}" alt="" loading="lazy">@endif
                    </a>
                    <div>
                        <h2>
                            @if ($item->product->active)
                                <a href="{{ route('products.show', $item->product) }}">{{ $item->product->name }}</a>
                            @else
                                {{ $item->product->name }}
                            @endif
                        </h2>
                        <p>{{ $item->product->short_description }}</p>
                        @if ($item->product->priceFor(auth()->user()) !== null)
                            <p class="basket-line-price">{{ number_format($item->product->priceFor(auth()->user()), 2, ',', ' ') }} zł / szt. · razem {{ number_format($item->product->priceFor(auth()->user()) * $item->quantity, 2, ',', ' ') }} zł</p>
                        @endif
                        @if (! $item->product->active)
                            <p class="basket-item-unavailable">Produkt nie jest obecnie dostępny w katalogu. Usuń go z koszyka, aby kontynuować.</p>
                        @else
                            @if ($item->product->stock_quantity !== null && $item->quantity > $item->product->stock_quantity)
                                <p class="basket-item-unavailable">Dostępna ilość uległa zmianie. Zaktualizuj koszyk lub usuń produkt.</p>
                            @endif
                            <form class="basket-quantity-form" method="POST" action="{{ route('basket.update', $item) }}">
                                @csrf
                                @method('PATCH')
                                <label for="quantity-{{ $item->id }}">Ilość</label>
                                <input id="quantity-{{ $item->id }}" name="quantity" type="number" min="{{ $item->product->minimum_order_quantity }}" max="{{ min(999, $item->product->stock_quantity ?? 999) }}" value="{{ $item->quantity }}" required>
                                <button class="text-link" type="submit">Zmień ilość</button>
                            </form>
                            @error('quantity') <span class="field-error">{{ $message }}</span> @enderror
                        @endif
                    </div>
                    <form method="POST" action="{{ route('basket.destroy', $item) }}">
                        @csrf
                        @method('DELETE')
                        <button class="remove-button" type="submit" aria-label="Usuń {{ $item->product->name }} z koszyka">Usuń</button>
                    </form>
                </article>
            @endforeach
        </div>
        @php
            $basketTotal = $items->sum(fn ($i) => $i->product->active ? ($i->product->priceFor(auth()->user()) ?? 0) * $i->quantity : 0);
        @endphp
        <div class="basket-next-step">
            @if ($basketTotal > 0)
                <p class="basket-total"><strong>Suma: {{ number_format($basketTotal, 2, ',', ' ') }} zł</strong>@if (auth()->user()->isSubscriber()) <small>(w tym zniżka subskrybenta −{{ config('features.subscriber_discount_percent') }}%)</small>@endif</p>
            @endif
            <p>Składanie zamówień i płatności online nie są jeszcze aktywne.</p>
            <form method="POST" action="{{ route('basket.inquiry') }}">
                @csrf
                <button class="button button-dark" type="submit">Zapytaj o produkty <span aria-hidden="true">↗</span></button>
            </form>
        </div>
    @endif
@endsection
