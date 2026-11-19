@php
    $accountItems = auth()->check() && auth()->user()->hasVerifiedEmail()
        ? (auth()->user()->basket()->with('items.product')->first()?->items ?? collect())
        : collect();
    $accountCount = $accountItems->sum('quantity');
@endphp
<div class="cart-backdrop" hidden></div>
<aside class="cart-panel" id="basket-popup" role="dialog" aria-modal="true" aria-labelledby="cart-title" aria-hidden="true" data-account-count="{{ $accountCount }}">
    <div class="cart-header">
        <div>
            <p class="eyebrow">Twój koszyk</p>
            <h2 id="cart-title">Koszyk <span class="cart-count">{{ $accountCount }}</span></h2>
        </div>
        <button class="cart-close" type="button" aria-label="Zamknij koszyk">×</button>
    </div>
    <p class="cart-intro">Wybrane produkty. Cenę i dostępność potwierdzi nasz zespół — to nie jest zamówienie.</p>

    @if ($accountItems->isNotEmpty())
        <div class="cart-account">
            <p class="cart-section-title">Na Twoim koncie</p>
            @foreach ($accountItems as $item)
                <div class="cart-line">
                    <strong>{{ $item->product->name }}</strong>
                    <div class="cart-line-actions"><span>{{ $item->quantity }} szt.</span></div>
                </div>
            @endforeach
            <a class="text-link cart-manage" href="{{ route('basket.show') }}">Zarządzaj koszykiem <span aria-hidden="true">↗</span></a>
        </div>
    @endif

    <div class="cart-items" aria-live="polite"></div>
    <p class="cart-empty">Twój koszyk jest jeszcze pusty.<br>Dodaj produkt, aby zapytać o współpracę.</p>
    <a class="button button-dark cart-inquiry" href="{{ route('contact') }}">Wyślij zapytanie <span aria-hidden="true">↗</span></a>
    <p class="cart-disclaimer">To zapytanie nie jest zamówieniem. Cenę i dostępność potwierdzi nasz zespół.</p>
</aside>
<p class="sr-only" role="status" aria-live="polite" id="announcement-status"></p>
