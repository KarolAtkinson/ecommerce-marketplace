@extends('auth.layout')

@section('title', 'Załóż konto')

@section('content')
    <h1 class="auth-title">Zostań<br><em>partnerem.</em></h1>
    @if (! $registrationEnabled)
        <p class="auth-description">Rejestracja ruszy po przygotowaniu i zatwierdzeniu warunków korzystania oraz polityki prywatności dla marketplace'u. Nie tworzymy kont przed publikacją tych dokumentów.</p>
        <div class="notice-panel">
            <strong>Zapytaj o współpracę już teraz</strong>
            <p>Napisz do naszego zespołu, a odpowiemy na pytania dotyczące produktu i oferty hurtowej.</p>
            <a class="button button-dark" href="mailto:kontakt@naturadala.pl?subject=Zapytanie%20o%20wsp%C3%B3%C5%82prac%C4%99">Napisz do nas <span aria-hidden="true">↗</span></a>
        </div>
    @else
        <p class="auth-description">Utwórz konto klienta lub zgłoś firmę do weryfikacji. Ceny hurtowe są dostępne dopiero po zatwierdzeniu profilu firmy.</p>
        <form class="auth-form" method="POST" action="{{ route('register.store') }}">
            @csrf
            <label for="name">Imię i nazwisko</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required maxlength="190">
            @error('name') <span class="field-error">{{ $message }}</span> @enderror

            <label for="email">Adres e-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required maxlength="190">
            @error('email') <span class="field-error">{{ $message }}</span> @enderror

            <label for="phone">Telefon <span class="field-optional">(opcjonalnie)</span></label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="32">

            <label for="account_type">Rodzaj konta</label>
            <select id="account_type" name="account_type" required>
                <option value="customer" @selected(old('account_type', 'customer') === 'customer')>Klient indywidualny</option>
                <option value="retailer" @selected(old('account_type') === 'retailer')>Firma / sprzedawca</option>
            </select>

            <div class="business-fields" @if(old('account_type') !== 'retailer') hidden @endif>
                <label for="business_name">Pełna nazwa firmy</label>
                <input id="business_name" name="business_name" type="text" value="{{ old('business_name') }}" autocomplete="organization" maxlength="190">
                @error('business_name') <span class="field-error">{{ $message }}</span> @enderror
                <label for="tax_identifier">NIP <span class="field-optional">(opcjonalnie)</span></label>
                <input id="tax_identifier" name="tax_identifier" type="text" value="{{ old('tax_identifier') }}" autocomplete="off" maxlength="32">
                <label for="business_phone">Telefon firmowy <span class="field-optional">(opcjonalnie)</span></label>
                <input id="business_phone" name="business_phone" type="tel" value="{{ old('business_phone') }}" maxlength="32">
                <p class="form-hint">Konto firmowe uzyska dostęp do warunków hurtowych dopiero po osobnej weryfikacji.</p>
            </div>

            <label for="password">Hasło</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
            @error('password') <span class="field-error">{{ $message }}</span> @enderror
            <label for="password_confirmation">Powtórz hasło</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>

            <label class="checkbox-label">
                <input id="terms-checkbox" name="terms_accepted" type="checkbox" value="1" @checked(old('terms_accepted')) required>
                <span>Akceptuję <a href="{{ route('legal.terms') }}" data-legal-open="terms" target="_blank" rel="noreferrer">warunki korzystania</a> oraz zapoznałem(-am) się z <a href="{{ route('legal.privacy') }}" data-legal-open="privacy" target="_blank" rel="noreferrer">polityką prywatności</a>.</span>
            </label>
            <div class="legal-read-buttons">
                <button class="button button-outline" type="button" data-legal-open="terms">Przeczytaj regulamin</button>
                <button class="button button-outline" type="button" data-legal-open="privacy">Przeczytaj politykę prywatności</button>
            </div>
            @error('terms_accepted') <span class="field-error">{{ $message }}</span> @enderror

            <label class="checkbox-label">
                <input name="marketing_consent" type="checkbox" value="1" @checked(old('marketing_consent'))>
                <span>Chcę otrzymywać informacje marketingowe. Zgodę mogę wycofać w dowolnym momencie.</span>
            </label>

            <button class="button button-dark auth-submit" type="submit">Utwórz konto <span aria-hidden="true">↗</span></button>
        </form>
    @endif
    <dialog class="legal-dialog" id="legal-dialog" aria-labelledby="legal-dialog-title">
        <div class="legal-dialog-head">
            <h2 id="legal-dialog-title">Dokument</h2>
            <button class="legal-dialog-close" type="button" data-legal-close aria-label="Zamknij">×</button>
        </div>
        <div class="legal-dialog-body" id="legal-dialog-body" tabindex="0"></div>
        <div class="legal-dialog-foot">
            <button class="button button-outline" type="button" data-legal-close>Zamknij</button>
            <button class="button button-dark" type="button" id="legal-dialog-accept">Akceptuję i zamykam</button>
        </div>
    </dialog>
    <script src="{{ asset('legal-modal.js') }}" defer></script>
    <p class="auth-switch">Masz już konto? <a href="{{ route('login') }}">Zaloguj się</a></p>
@endsection
