@extends('auth.layout')

@section('title', $product->exists ? 'Edytowanie produktu' : 'Nowy produkt')

@section('content')
    <div class="admin-header-row">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Administracja</p>
            <h1 class="auth-title">{{ $product->exists ? 'Edytuj' : 'Dodaj' }}<br><em>produkt.</em></h1>
        </div>
        <a class="text-link" href="{{ route('admin.products.index') }}">Powrót do listy</a>
    </div>

    <form class="auth-form" method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="form-grid two-col">
            <label>
                SKU
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required>
                @error('sku') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>
                Slug
                <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" required>
                @error('slug') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label class="full-width">
                Nazwa produktu
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required>
                @error('name') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label class="full-width">
                Krótki opis
                <textarea name="short_description" rows="3" required>{{ old('short_description', $product->short_description) }}</textarea>
                @error('short_description') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label class="full-width">
                Opis produktu
                <textarea name="description" rows="6" required>{{ old('description', $product->description) }}</textarea>
                <span class="form-hint">Oddziel akapity pustym wierszem. Pierwszy akapit będzie widoczny od razu; pozostałe rozwiniesz przyciskiem „więcej”.</span>
                @error('description') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>
                Rozmiar / etykieta
                <input type="text" name="size_label" value="{{ old('size_label', $product->size_label) }}">
                @error('size_label') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>
                URL obrazka
                <input type="url" name="image_url" value="{{ old('image_url', $product->image_url) }}">
                @error('image_url') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>
                Cena detaliczna (zł)
                <input type="number" step="0.01" min="0" name="retail_price" value="{{ old('retail_price', $product->retail_price) }}">
                @error('retail_price') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>
                Stan magazynowy
                <input type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}">
                @error('stock_quantity') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>
                Min. ilość zamówienia
                <input type="number" min="1" name="minimum_order_quantity" value="{{ old('minimum_order_quantity', $product->minimum_order_quantity) }}" required>
                @error('minimum_order_quantity') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>
                Kolejność sortowania
                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" required>
                @error('sort_order') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label class="checkbox-row">
                <input type="checkbox" name="active" value="1" {{ old('active', $product->active) ? 'checked' : '' }}>
                Produkt aktywny i widoczny w katalogu
            </label>
        </div>

        <fieldset class="price-tier-fieldset">
            <legend>Ceny hurtowe</legend>
            @php($rows = old('wholesale_prices', $wholesalePrices))
            @error('wholesale_prices') <span class="field-error">{{ $message }}</span> @enderror
            <div class="price-tier-list" data-price-tier-list>
                @foreach ($rows as $index => $price)
                    <div class="price-tier-row">
                        <input type="hidden" name="wholesale_prices[{{ $index }}][id]" value="{{ $price['id'] ?? '' }}">
                        <label>
                            Min. ilość
                            <input type="number" min="{{ old('minimum_order_quantity', $product->minimum_order_quantity ?? 1) }}" name="wholesale_prices[{{ $index }}][minimum_quantity]" value="{{ old('wholesale_prices.' . $index . '.minimum_quantity', $price['minimum_quantity'] ?? 1) }}">
                            @error('wholesale_prices.' . $index . '.minimum_quantity') <span class="field-error">{{ $message }}</span> @enderror
                        </label>
                        <label>
                            Cena netto (zł)
                            <input type="number" step="0.01" min="0" name="wholesale_prices[{{ $index }}][unit_price]" value="{{ old('wholesale_prices.' . $index . '.unit_price', $price['unit_price'] ?? '') }}">
                            @error('wholesale_prices.' . $index . '.unit_price') <span class="field-error">{{ $message }}</span> @enderror
                        </label>
                        <label>
                            Od dnia
                            <input type="date" name="wholesale_prices[{{ $index }}][starts_at]" value="{{ old('wholesale_prices.' . $index . '.starts_at', $price['starts_at'] ?? '') }}">
                            @error('wholesale_prices.' . $index . '.starts_at') <span class="field-error">{{ $message }}</span> @enderror
                        </label>
                        <label>
                            Do dnia
                            <input type="date" name="wholesale_prices[{{ $index }}][ends_at]" value="{{ old('wholesale_prices.' . $index . '.ends_at', $price['ends_at'] ?? '') }}">
                            @error('wholesale_prices.' . $index . '.ends_at') <span class="field-error">{{ $message }}</span> @enderror
                        </label>
                        <button class="button button-outline button-small" type="button" data-remove-price-tier hidden>Usuń próg</button>
                    </div>
                @endforeach
            </div>
            <template data-price-tier-template>
                <div class="price-tier-row">
                    <input type="hidden" name="wholesale_prices[__INDEX__][id]" value="">
                    <label>
                        Min. ilość
                        <input type="number" min="{{ old('minimum_order_quantity', $product->minimum_order_quantity ?? 1) }}" name="wholesale_prices[__INDEX__][minimum_quantity]" value="{{ $product->minimum_order_quantity ?? 1 }}">
                    </label>
                    <label>
                        Cena netto (zł)
                        <input type="number" step="0.01" min="0" name="wholesale_prices[__INDEX__][unit_price]" value="">
                    </label>
                    <label>
                        Od dnia
                        <input type="date" name="wholesale_prices[__INDEX__][starts_at]" value="">
                    </label>
                    <label>
                        Do dnia
                        <input type="date" name="wholesale_prices[__INDEX__][ends_at]" value="">
                    </label>
                    <button class="button button-outline button-small" type="button" data-remove-price-tier>Usuń próg</button>
                </div>
            </template>
            <button class="button button-outline button-small" type="button" data-add-price-tier hidden>Dodaj próg cenowy</button>
        </fieldset>

        <div class="form-actions">
            <button class="button button-dark" type="submit">{{ $product->exists ? 'Zapisz zmiany' : 'Dodaj produkt' }}</button>
            <a class="button button-outline" href="{{ route('admin.products.index') }}">Anuluj</a>
        </div>
    </form>
@endsection
