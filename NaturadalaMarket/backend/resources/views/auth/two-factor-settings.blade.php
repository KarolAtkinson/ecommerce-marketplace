@extends('auth.layout')

@section('title', 'Uwierzytelnianie dwuetapowe')

@section('content')
    <h1 class="auth-title">Dodatkowa<br><em>ochrona.</em></h1>
    <p class="auth-description">Aplikacja uwierzytelniająca generuje jednorazowe, sześciocyfrowe kody. Kod jest wymagany przy każdym nowym logowaniu.</p>

    @if (session('status') === 'mfa-disabled')
        <p class="status-message">Uwierzytelnianie dwuetapowe zostało wyłączone.</p>
    @endif

    @if (session()->has('recoveryCodes'))
        <div class="recovery-panel" role="status">
            <strong>Zapisz kody odzyskiwania</strong>
            <p>Każdy kod można wykorzystać tylko raz. Ta lista nie zostanie ponownie wyświetlona.</p>
            <ul>
                @foreach (session('recoveryCodes') as $code)
                    <li><code>{{ $code }}</code></li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($enabled)
        <div class="notice-panel mfa-enabled">
            <strong>Uwierzytelnianie jest włączone.</strong>
            <p>Zachowaj kody odzyskiwania w bezpiecznym miejscu. Możesz wygenerować nowy zestaw, a poprzednie kody przestaną działać.</p>
        </div>
        <form class="auth-form" method="POST" action="{{ route('mfa.recovery-codes.regenerate') }}">
            @csrf
            <label for="recovery_current_password">Bieżące hasło</label>
            <input id="recovery_current_password" name="current_password" type="password" autocomplete="current-password" required>
            @error('current_password') <span class="field-error">{{ $message }}</span> @enderror
            <label for="recovery_code">Aktualny kod z aplikacji</label>
            <input id="recovery_code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
            @error('code') <span class="field-error">{{ $message }}</span> @enderror
            <button class="button button-outline auth-submit" type="submit">Wygeneruj nowe kody odzyskiwania</button>
        </form>
        <form class="auth-form" method="POST" action="{{ route('mfa.destroy') }}">
            @csrf
            @method('DELETE')
            <label for="password">Bieżące hasło</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <label for="code">Aktualny kod z aplikacji</label>
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
            <button class="button button-outline auth-submit" type="submit">Wyłącz dodatkową ochronę</button>
        </form>
    @else
        <ol class="mfa-steps">
            <li>Otwórz aplikację uwierzytelniającą na telefonie i dodaj nowe konto.</li>
            <li>Wybierz ręczne wpisanie klucza i wpisz poniższy klucz jako konto czasowe (TOTP).</li>
            <li>Wpisz wygenerowany kod, aby potwierdzić konfigurację.</li>
        </ol>
        <div class="mfa-secret-wrap">
            <span>Klucz do aplikacji uwierzytelniającej</span>
            <code class="mfa-secret">{{ $secret }}</code>
            <p>Nie udostępniaj tego klucza. Jest tymczasowy i wygaśnie po 10 minutach.</p>
        </div>
        <form class="auth-form" method="POST" action="{{ route('mfa.store') }}">
            @csrf
            <label for="password">Potwierdź bieżącym hasłem</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <label for="code">Sześciocyfrowy kod z aplikacji</label>
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
            <button class="button button-dark auth-submit" type="submit">Włącz dodatkową ochronę <span aria-hidden="true">↗</span></button>
        </form>
    @endif

    <p class="auth-switch"><a href="{{ route('account.show') }}">Wróć do konta</a></p>
@endsection
