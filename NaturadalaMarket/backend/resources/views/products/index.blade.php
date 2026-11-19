@extends('products.layout')

@section('title', 'Produkty Naturadała')
@section('description', 'Poznaj ofertę Naturadała dla klientów i partnerów biznesowych. Ceny hurtowe widoczne po zatwierdzeniu konta firmy.')

@section('content')
    <section class="catalogue-hero">
        <p class="eyebrow"><span class="eyebrow-line"></span> Oferta Naturadała</p>
        <div class="catalogue-intro">
            <h1>Prosty skład.<br><em>Dobry wybór.</em></h1>
            <p>Odkryj koncentrat kolagenowy Naturadała. Partnerzy biznesowi mogą złożyć wniosek o dostęp do warunków hurtowych.</p>
        </div>
    </section>

    <section class="catalogue-section" aria-labelledby="catalogue-title">
        <div class="catalogue-heading">
            <div>
                <p class="eyebrow"><span class="eyebrow-line"></span> Starannie opracowany produkt</p>
                <h2 id="catalogue-title">Nasza oferta</h2>
            </div>
            @if ($approvedRetailer)
                <span class="audience-badge">Konto partnera zatwierdzone</span>
            @elseif (auth()->check() && auth()->user()->retailerProfile?->verification_status === 'pending')
                <span class="audience-badge">Wniosek firmy w weryfikacji</span>
            @endif
        </div>

        <form class="catalogue-toolbar" method="GET" action="{{ route('products.index') }}">
            <label class="search-field">
                <span class="sr-only">Szukaj produktu</span>
                <input type="search" name="q" value="{{ $search }}" maxlength="120" placeholder="Szukaj produktu, opisu lub składnika" aria-label="Szukaj produktu">
            </label>
            <label class="sort-field">
                <span>Sortowanie</span>
                <select name="sort">
                    <option value="default" {{ $sort === 'default' ? 'selected' : '' }}>Najczęściej wybierane</option>
                    <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Nazwa A–Z</option>
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Najnowsze</option>
                </select>
            </label>
            <button class="button button-outline button-small" type="submit">Filtruj</button>
            @if ($search !== '' || $sort !== 'default')
                <a class="text-link catalogue-clear" href="{{ route('products.index') }}">Wyczyść filtry</a>
            @endif
        </form>
        @error('q') <p class="field-error" role="alert">{{ $message }}</p> @enderror
        @error('sort') <p class="field-error" role="alert">{{ $message }}</p> @enderror

        @if ($products->isEmpty())
            <div class="empty-state">
                <p>Brak produktów pasujących do filtrów. Spróbuj zmienić frazę lub wyczyścić wyszukiwanie.</p>
                @if ($search !== '' || $sort !== 'default')
                    <a class="button button-outline button-small" href="{{ route('products.index') }}">Pokaż wszystkie produkty</a>
                @endif
            </div>
        @else
            <p class="catalogue-results" role="status">
                Wyświetlono {{ $products->firstItem() }}–{{ $products->lastItem() }} z {{ $products->total() }} produktów
                @if ($search !== '')
                    dla frazy „{{ $search }}”
                @endif
            </p>
            <div class="catalogue-grid">
                @foreach ($products as $product)
                    @include('products.card', [
                        'product' => $product,
                        'approvedRetailer' => $approvedRetailer,
                        'wishlisted' => in_array($product->id, $wishlistedIds, true),
                    ])
                @endforeach
            </div>
            {{ $products->links() }}
        @endif
    </section>

    @unless ($approvedRetailer || (config('features.retailer_maintenance') && ! auth()->user()?->hasRole('admin')))
        <section class="catalogue-callout">
            <div>
                <p class="eyebrow">Dla sklepów i profesjonalistów</p>
                <h2>Chcesz poznać<br><em>warunki hurtowe?</em></h2>
            </div>
            <div class="catalogue-callout-copy">
                <p>Ceny partnerskie udostępniamy wyłącznie zweryfikowanym firmom. Zaloguj się i zgłoś swoją firmę do weryfikacji.</p>
                <a class="button button-light" href="{{ auth()->check() && auth()->user()->hasVerifiedEmail() ? route('retailer.application') : route('register') }}">Informacje o współpracy <span aria-hidden="true">↗</span></a>
            </div>
        </section>
    @endunless
@endsection
