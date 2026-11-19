@extends('auth.layout')

@section('title', 'Zaloguj się')

@section('content')
    <h1 class="auth-title">Miło Cię<br><em>widzieć.</em></h1>
    <p class="auth-description">Zaloguj się do swojego konta Naturadała.</p>
    @if (session('status') === 'passwords.sent')
        <p class="status-message">Jeśli konto istnieje, wysłaliśmy wiadomość z dalszymi instrukcjami.</p>
    @elseif (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif
    <form class="auth-form" method="POST" action="{{ route('login.store') }}">
        @csrf
        <label for="email">Adres e-mail</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        @error('email') <span class="field-error">{{ $message }}</span> @enderror
        <label for="password">Hasło</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        @error('password') <span class="field-error">{{ $message }}</span> @enderror
        <label class="checkbox-label">
            <input name="remember" type="checkbox" value="1">
            <span>Nie wylogowuj mnie</span>
        </label>
        <button class="button button-dark auth-submit" type="submit">Zaloguj się <span aria-hidden="true">↗</span></button>
    </form>
    <div class="auth-links">
        <a href="{{ route('password.request') }}">Nie pamiętasz hasła?</a>
        <a href="{{ route('register') }}">Załóż konto</a>
    </div>
@endsection
