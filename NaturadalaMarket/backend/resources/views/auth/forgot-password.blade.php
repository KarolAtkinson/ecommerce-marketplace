@extends('auth.layout')

@section('title', 'Resetowanie hasła')

@section('content')
    <h1 class="auth-title">Odzyskaj<br><em>dostęp.</em></h1>
    <p class="auth-description">Podaj adres e-mail powiązany z kontem. Jeśli konto istnieje i może korzystać z usługi, otrzymasz link do ustawienia nowego hasła.</p>
    @if (session('status') === 'password-link-requested')
        <p class="status-message">Jeśli konto istnieje, wysłaliśmy wiadomość z dalszymi instrukcjami.</p>
    @endif
    <form class="auth-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <label for="email">Adres e-mail</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        @error('email') <span class="field-error">{{ $message }}</span> @enderror
        <button class="button button-dark auth-submit" type="submit">Wyślij link resetujący <span aria-hidden="true">↗</span></button>
    </form>
    <p class="auth-switch"><a href="{{ route('login') }}">Wróć do logowania</a></p>
@endsection
