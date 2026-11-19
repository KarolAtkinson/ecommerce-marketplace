@extends('auth.layout')

@section('title', $address->exists ? 'Edytuj adres' : 'Dodaj adres')

@section('content')
    <div class="admin-header-row">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Moje konto</p>
            <h1 class="auth-title">{{ $address->exists ? 'Edytuj' : 'Dodaj' }}<br><em>adres.</em></h1>
        </div>
        <a class="text-link" href="{{ route('addresses.index') }}">Powrót do adresów</a>
    </div>

    <form class="auth-form" method="POST" data-address-form data-suggest-url="{{ route('addresses.suggest') }}" action="{{ $address->exists ? route('addresses.update', $address) : route('addresses.store') }}">
        @csrf
        @if ($address->exists)
            @method('PUT')
        @endif

        <div class="address-search" data-address-search hidden>
            <label for="address_search">Wyszukaj adres</label>
            <input id="address_search" type="text" maxlength="120" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="address_suggestions" aria-autocomplete="list" placeholder="Wpisz ulicę, numer i miejscowość">
            <ul id="address_suggestions" class="address-suggestions" role="listbox" hidden></ul>
            <span class="field-hint">Wybierz adres z listy. Ulica, kod pocztowy, miejscowość i region uzupełnią się automatycznie i można je zmienić tylko wybierając inną podpowiedź.</span>
            <span class="field-hint" data-address-status role="status"></span>
        </div>

        <label for="label">Etykieta <span class="field-optional">(opcjonalnie)</span></label>
        <input id="label" name="label" type="text" maxlength="80" value="{{ old('label', $address->label) }}" autocomplete="off">
        @error('label') <span class="field-error">{{ $message }}</span> @enderror

        <label for="recipient_name">Odbiorca</label>
        <input id="recipient_name" name="recipient_name" type="text" maxlength="190" value="{{ old('recipient_name', $address->recipient_name) }}" autocomplete="name" required>
        @error('recipient_name') <span class="field-error">{{ $message }}</span> @enderror

        <label for="company_name">Firma <span class="field-optional">(opcjonalnie)</span></label>
        <input id="company_name" name="company_name" type="text" maxlength="190" value="{{ old('company_name', $address->company_name) }}" autocomplete="organization">
        @error('company_name') <span class="field-error">{{ $message }}</span> @enderror

        <label for="street_line_1">Ulica i numer</label>
        <input id="street_line_1" name="street_line_1" data-address-locked type="text" maxlength="190" value="{{ old('street_line_1', $address->street_line_1) }}" autocomplete="address-line1" required>
        @error('street_line_1') <span class="field-error">{{ $message }}</span> @enderror

        <label for="street_line_2">Dodatkowe informacje <span class="field-optional">(opcjonalnie)</span></label>
        <input id="street_line_2" name="street_line_2" type="text" maxlength="190" value="{{ old('street_line_2', $address->street_line_2) }}" autocomplete="address-line2">
        @error('street_line_2') <span class="field-error">{{ $message }}</span> @enderror

        <div class="form-grid two-col">
            <div class="form-grid-field">
                <label for="postal_code">Kod pocztowy</label>
                <input id="postal_code" name="postal_code" data-address-locked type="text" maxlength="24" value="{{ old('postal_code', $address->postal_code) }}" autocomplete="postal-code" required>
                @error('postal_code') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-grid-field">
                <label for="city">Miejscowość</label>
                <input id="city" name="city" data-address-locked type="text" maxlength="120" value="{{ old('city', $address->city) }}" autocomplete="address-level2" required>
                @error('city') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <label for="region">Region / województwo <span class="field-optional">(opcjonalnie)</span></label>
        <input id="region" name="region" data-address-locked type="text" maxlength="120" value="{{ old('region', $address->region) }}" autocomplete="address-level1">
        @error('region') <span class="field-error">{{ $message }}</span> @enderror

        <label for="country_code">Kod kraju</label>
        <input id="country_code" name="country_code" type="text" minlength="2" maxlength="2" pattern="[A-Za-z]{2}" value="{{ old('country_code', $address->country_code ?: 'PL') }}" autocomplete="country" required>
        <span class="field-hint">Dwuliterowy kod kraju, np. PL lub DE.</span>
        @error('country_code') <span class="field-error">{{ $message }}</span> @enderror

        <label for="phone">Telefon <span class="field-optional">(opcjonalnie)</span></label>
        <input id="phone" name="phone" type="tel" maxlength="32" value="{{ old('phone', $address->phone) }}" autocomplete="tel">
        @error('phone') <span class="field-error">{{ $message }}</span> @enderror

        <label class="checkbox-label">
            <input type="checkbox" name="is_default_billing" value="1" @checked(old('is_default_billing', $address->is_default_billing))>
            <span>Ustaw jako domyślny adres rozliczeniowy</span>
        </label>
        <label class="checkbox-label">
            <input type="checkbox" name="is_default_shipping" value="1" @checked(old('is_default_shipping', $address->is_default_shipping))>
            <span>Ustaw jako domyślny adres dostawy</span>
        </label>

        <button class="button button-dark auth-submit" type="submit">Zapisz adres</button>
    </form>
    <script src="{{ asset('address-lookup.js') }}" defer></script>
@endsection
