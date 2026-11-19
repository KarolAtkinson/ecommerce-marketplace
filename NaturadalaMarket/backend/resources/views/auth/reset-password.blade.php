@extends('auth.layout')

@section('title', 'Ustaw nowe hasło')

@section('content')
    <h1 class="auth-title">Ustaw nowe<br><em>hasło.</em></h1>
    <p class="auth-description">Wybierz nowe, unikalne hasło do swojego konta.</p>
    <form class="auth-form" method="POST" action="{{ route('password.store') }}">
        @csrf
        <input name="token" type="hidden" value="{{ $token }}">
        <label for="email">Adres e-mail</label>
        <input id="email" name="email" type="email" value="{{ old('email', $requestEmail) }}" autocomplete="email" required>
        @error('email') <span class="field-error">{{ $message }}</span> @enderror
        <label for="password">Nowe hasło</label>
        <input id="password" name="password" type="password" autocomplete="new-password" required>
        @error('password') <span class="field-error">{{ $message }}</span> @enderror
        <label for="password_confirmation">Powtórz nowe hasło</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        <button class="button button-dark auth-submit" type="submit">Zmień hasło <span aria-hidden="true">↗</span></button>
    </form>
@endsection
