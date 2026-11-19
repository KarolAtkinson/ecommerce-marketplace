@extends('retailer.layout')

@section('title', 'Przestrzeń partnera')

@section('content')
    <section class="retailer-welcome">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Naturadała · konto partnera</p>
            <h1>Przestrzeń<br><em>handlowa.</em></h1>
            <p>Witaj, {{ $profile->legal_business_name }}. Tutaj znajdziesz katalog, warunki partnerskie i przypisane informacje o współpracy.</p>
        </div>
        <span class="retailer-status-badge">Konto zatwierdzone</span>
    </section>

    <section class="retailer-record-grid" aria-label="Podsumowanie firmy">
        <article class="retailer-record-card">
            <span>Firma</span>
            <strong>{{ $profile->legal_business_name }}</strong>
            <small>{{ $profile->tax_identifier ?: 'Identyfikator firmy nieuzupełniony' }}</small>
        </article>
        <article class="retailer-record-card">
            <span>Osoba kontaktowa</span>
            <strong>{{ auth()->user()->name }}</strong>
            <small>{{ auth()->user()->email }}</small>
        </article>
        <article class="retailer-record-card">
            <span>Rekordy umów</span>
            <strong>{{ $profile->contracts->count() }}</strong>
            <small>Metadane umów, nie dokumenty prawne</small>
        </article>
    </section>

    <section class="retailer-data-section" aria-labelledby="retailer-catalogue-heading">
        <div class="retailer-section-heading">
            <div>
                <p class="eyebrow"><span class="eyebrow-line"></span> Baza produktów</p>
                <h2 id="retailer-catalogue-heading">Katalog hurtowy</h2>
            </div>
            <a class="button button-dark" href="{{ route('basket.show') }}">Otwórz zapytanie <span aria-hidden="true">↗</span></a>
        </div>
        <form class="retailer-search" method="GET" action="{{ route('retailer.workspace') }}">
            <label for="retailer-search">Szukaj po nazwie, SKU lub opisie</label>
            <input id="retailer-search" type="search" name="q" value="{{ $search }}" maxlength="120" placeholder="Nazwa produktu lub SKU">
            <button class="button button-outline button-small" type="submit">Szukaj</button>
            @if ($search !== '')
                <a class="text-link" href="{{ route('retailer.workspace') }}">Wyczyść</a>
            @endif
        </form>
        @error('q') <p class="field-error" role="alert">{{ $message }}</p> @enderror
        <div class="retailer-table-wrap">
            <table class="retailer-table">
                <caption class="sr-only">Aktywne produkty i hurtowe warunki handlowe dla zatwierdzonej firmy</caption>
                <thead>
                    <tr>
                        <th scope="col">Produkt</th>
                        <th scope="col">SKU</th>
                        <th scope="col">Minimalne zamówienie</th>
                        <th scope="col">Cena partnerska</th>
                        <th scope="col">Dostępność</th>
                        <th scope="col">Działanie</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <th scope="row">
                                <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                                <small>{{ $product->short_description }}</small>
                            </th>
                            <td><code>{{ $product->sku }}</code></td>
                            <td>{{ $product->minimum_order_quantity }} szt.</td>
                            <td>
                                @forelse ($product->wholesalePrices as $price)
                                    <span class="retailer-price">{{ number_format((float) $price->unit_price, 2, ',', ' ') }} zł</span>
                                    <small>netto · od {{ $price->minimum_quantity }} szt.</small>
                                @empty
                                    <span>Zapytaj o ofertę</span>
                                @endforelse
                            </td>
                            <td>{{ $product->stock_quantity === null ? 'Potwierdź przed zamówieniem' : $product->stock_quantity.' szt.' }}</td>
                            <td>
                                <form method="POST" action="{{ route('basket.store', $product) }}">
                                    @csrf
                                    <input type="hidden" name="quantity" value="{{ $product->minimum_order_quantity }}">
                                    <button class="button button-outline button-small" type="submit">Dodaj do zapytania</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Brak produktów pasujących do wyszukiwania.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $products->links() }}
    </section>

    <section class="retailer-data-section" aria-labelledby="retailer-contract-heading">
        <div class="retailer-section-heading">
            <div>
                <p class="eyebrow"><span class="eyebrow-line"></span> Rejestr współpracy</p>
                <h2 id="retailer-contract-heading">Umowy i ustalenia</h2>
            </div>
        </div>
        <p class="retailer-disclaimer">Widoczne są wyłącznie metadane przypisane do Twojej firmy. Treść umów i warunki prawne nie są przechowywane ani tworzone w tym module.</p>
        <div class="retailer-table-wrap">
            <table class="retailer-table">
                <caption class="sr-only">Metadane umów przypisanych do tej firmy</caption>
                <thead><tr><th scope="col">Numer</th><th scope="col">Tytuł / typ</th><th scope="col">Właściciel po stronie Naturadała</th><th scope="col">Okres</th><th scope="col">Status</th></tr></thead>
                <tbody>
                    @forelse ($profile->contracts as $contract)
                        <tr>
                            <th scope="row"><code>{{ $contract->reference }}</code></th>
                            <td>{{ $contract->title }}<small>{{ $contract->contract_type }}</small></td>
                            <td>{{ $contract->internalOwner?->name ?: 'Nieprzypisany' }}</td>
                            <td>{{ $contract->effective_date?->format('d.m.Y') ?: '—' }} – {{ $contract->expiry_date?->format('d.m.Y') ?: '—' }}</td>
                            <td>{{ ucfirst($contract->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Nie przypisano jeszcze rekordów umów do tej firmy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
