@extends('auth.layout')

@section('title', 'Dane konta')

@section('content')
    <div class="admin-header-row">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Moje konto</p>
            <h1 class="auth-title">Dane<br><em>konta.</em></h1>
        </div>
        <a class="text-link" href="{{ route('account.show') }}">Powrót do konta</a>
    </div>

    <p class="auth-description">Zaktualizuj imię i nazwisko lub numer telefonu. Zmiana adresu e-mail wymaga potwierdzenia nowego adresu.</p>
    @if (session('status') === 'email-unchanged')
        <p class="status-message">Ten adres e-mail jest już przypisany do konta.</p>
    @endif

    <form class="auth-form" method="POST" action="{{ route('account.profile.update') }}">
        @csrf
        @method('PUT')

        <label for="name">Imię i nazwisko</label>
        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="190" autocomplete="name" required>
        @error('name') <span class="field-error">{{ $message }}</span> @enderror

        <label for="email">Adres e-mail</label>
        <input id="email" type="email" value="{{ $user->email }}" autocomplete="email" disabled>
        <span class="field-hint">Aby zmienić adres, użyj osobnego formularza poniżej. Dostęp do konta będzie wymagał potwierdzenia nowego adresu.</span>

        <label for="phone">Telefon <span class="field-optional">(opcjonalnie)</span></label>
        <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" maxlength="32" autocomplete="tel">
        @error('phone') <span class="field-error">{{ $message }}</span> @enderror

        <button class="button button-dark auth-submit" type="submit">Zapisz dane</button>
    </form>

    <form class="auth-form" method="POST" action="{{ route('account.profile.email.update') }}">
        @csrf
        @method('PUT')

        <p class="eyebrow">Zmiana adresu e-mail</p>
        <label for="new_email">Nowy adres e-mail</label>
        <input id="new_email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required>
        @error('email') <span class="field-error">{{ $message }}</span> @enderror

        <label for="email_current_password">Potwierdź bieżącym hasłem</label>
        <input id="email_current_password" name="current_password" type="password" autocomplete="current-password" required>
        @error('current_password') <span class="field-error">{{ $message }}</span> @enderror

        <button class="button button-dark auth-submit" type="submit">Zmień adres i wyślij link weryfikacyjny</button>
    </form>
@endsection
