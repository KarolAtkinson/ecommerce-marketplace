@extends('auth.layout')

@section('title', 'Kontakt')

@section('content')
    <h1 class="auth-title">Zapytaj<br><em>o ofertę.</em></h1>
    <p class="auth-description">Wypełnij formularz, a nasz zespół odpowie w sprawie współpracy hurtowej, logistyki i cen.</p>

    @if (session('status') === 'inquiry-sent')
        <p class="status-message" data-inquiry-sent>Zapytanie zostało wysłane. Odpowiemy na adres podany w formularzu.</p>
    @endif

    <form class="auth-form" id="inquiry-form" method="POST" action="{{ route('contact.store') }}">
        @csrf
        <input type="hidden" id="inquiry-products" name="products" value="{{ old('products', json_encode($initialProducts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}">

        <label for="name">Imię i nazwisko</label>
        <input id="name" name="name" type="text" value="{{ old('name', $contactDefaults['name']) }}" required maxlength="190">
        @error('name') <span class="field-error">{{ $message }}</span> @enderror

        <label for="email">Adres e-mail</label>
        <input id="email" name="email" type="email" value="{{ old('email', $contactDefaults['email']) }}" required maxlength="190">
        @error('email') <span class="field-error">{{ $message }}</span> @enderror

        <label for="company_name">Nazwa firmy <span class="field-optional">(opcjonalnie)</span></label>
        <input id="company_name" name="company_name" type="text" value="{{ old('company_name', $contactDefaults['company_name']) }}" maxlength="190">

        <label for="phone">Telefon <span class="field-optional">(opcjonalnie)</span></label>
        <input id="phone" name="phone" type="tel" value="{{ old('phone', $contactDefaults['phone']) }}" maxlength="32">

        <div class="inquiry-product-summary" id="inquiry-product-summary" aria-live="polite">
            <strong>Produkty z Twojej listy</strong>
            <p>Możesz wysłać także ogólne zapytanie bez wybranych produktów.</p>
        </div>

        <label for="product_details">Produkty lub szczegóły zapytania <span class="field-optional">(opcjonalnie)</span></label>
        <textarea id="product_details" name="product_details" rows="3" maxlength="2000" placeholder="Opisz dodatkowe produkty lub ilości, o które chcesz zapytać.">{{ old('product_details') }}</textarea>
        @error('products') <span class="field-error">{{ $message }}</span> @enderror
        @error('product_details') <span class="field-error">{{ $message }}</span> @enderror

        <label for="message">Dodatkowe informacje</label>
        <textarea id="message" name="message" rows="5" maxlength="2000" placeholder="Opisz wymagania, planowane zamówienie lub zakres współpracy.">{{ old('message') }}</textarea>
        @error('message') <span class="field-error">{{ $message }}</span> @enderror

        <button class="button button-dark auth-submit" type="submit">Wyślij zapytanie <span aria-hidden="true">↗</span></button>
    </form>
@endsection
