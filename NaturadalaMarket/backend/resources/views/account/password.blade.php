@extends('auth.layout')

@section('title', 'Zmień hasło')

@section('content')
    <div class="admin-header-row">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Moje konto</p>
            <h1 class="auth-title">Zmień<br><em>hasło.</em></h1>
        </div>
        <a class="text-link" href="{{ route('account.show') }}">Powrót do konta</a>
    </div>

    <p class="auth-description">Podaj obecne hasło, a następnie wybierz nowe, unikalne hasło.</p>

    <form class="auth-form" method="POST" action="{{ route('account.password.update') }}">
        @csrf
        @method('PUT')

        <label for="current_password">Obecne hasło</label>
        <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
        @error('current_password') <span class="field-error">{{ $message }}</span> @enderror

        <label for="password">Nowe hasło</label>
        <input id="password" name="password" type="password" autocomplete="new-password" required>
        @error('password') <span class="field-error">{{ $message }}</span> @enderror

        <label for="password_confirmation">Powtórz nowe hasło</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>

        <button class="button button-dark auth-submit" type="submit">Zapisz nowe hasło</button>
    </form>
@endsection
