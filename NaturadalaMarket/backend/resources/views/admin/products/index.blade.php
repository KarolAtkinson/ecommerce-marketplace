@extends('auth.layout')

@section('title', 'Zarządzanie katalogiem')

@section('content')
    <div class="admin-header-row">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Administracja</p>
            <h1 class="auth-title">Katalog<br><em>produktów.</em></h1>
        </div>
        <a class="button button-dark" href="{{ route('admin.products.create') }}">Dodaj produkt</a>
    </div>

    @if (session('status') === 'product-saved')
        <p class="status-message">Zmiany katalogu zostały zapisane.</p>
    @elseif (session('status') === 'product-deleted')
        <p class="status-message">Produkt został usunięty.</p>
    @elseif (session('status') === 'product-in-use')
        <p class="status-message" role="alert">Produkt znajduje się w koszykach lub na listach życzeń. Dezaktywuj go zamiast usuwać, aby zachować zapisane pozycje klientów.</p>
    @endif

    <form class="inquiry-filters" method="GET" action="{{ route('admin.products.index') }}">
        <label>
            <span class="sr-only">Szukaj produktów</span>
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" placeholder="Szukaj nazwy, SKU lub sluga">
        </label>
        <label>
            <span class="sr-only">Filtruj według widoczności</span>
            <select name="active">
                <option value="">Wszystkie produkty</option>
                <option value="1" @selected(($filters['active'] ?? '') === '1')>Aktywne</option>
                <option value="0" @selected(($filters['active'] ?? '') === '0')>Nieaktywne</option>
            </select>
        </label>
        <button class="button button-outline button-small" type="submit">Filtruj</button>
        @if (($filters['q'] ?? '') !== '' || ($filters['active'] ?? '') !== '')
            <a class="text-link" href="{{ route('admin.products.index') }}">Wyczyść</a>
        @endif
    </form>

    <p class="catalogue-results" role="status">Wyświetlono {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} z {{ $products->total() }} produktów</p>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Produkt</th>
                    <th>Aktywny</th>
                    <th>Hurtowe</th>
                    <th>Cena</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <strong>{{ $product->name }}</strong><br>
                            <span>{{ $product->sku }}</span>
                        </td>
                        <td>{{ $product->active ? 'Tak' : 'Nie' }}</td>
                        <td>{{ $product->wholesalePrices->count() }}</td>
                        <td>{{ $product->retail_price !== null ? number_format((float) $product->retail_price, 2, ',', ' ') . ' zł' : '—' }}</td>
                        <td class="admin-actions">
                            <a class="button button-outline" href="{{ route('admin.products.edit', $product) }}">Edytuj</a>
                            @if ($product->basket_items_count === 0 && $product->wishlist_items_count === 0)
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Trwale usunąć produkt z katalogu?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button button-danger" type="submit">Usuń</button>
                                </form>
                            @else
                                <span class="field-error">Zapisany przez klientów — dezaktywuj zamiast usuwać</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Nie znaleziono produktów spełniających kryteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
@endsection
