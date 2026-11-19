@extends('auth.layout')

@section('title', 'Potwierdź logowanie')

@section('content')
    <h1 class="auth-title">Potwierdź<br><em>logowanie.</em></h1>
    <p class="auth-description">Wpisz aktualny kod z aplikacji uwierzytelniającej albo jeden z jednorazowych kodów odzyskiwania. Ta weryfikacja wygaśnie za kilka minut.</p>
    <form class="auth-form" method="POST" action="{{ route('two-factor.login.store') }}">
        @csrf
        <label for="code">Kod weryfikacyjny</label>
        <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="32" required autofocus>
        @error('code') <span class="field-error">{{ $message }}</span> @enderror
        <button class="button button-dark auth-submit" type="submit">Potwierdź logowanie <span aria-hidden="true">↗</span></button>
    </form>
    <p class="auth-switch">Nie możesz się zalogować? <a href="mailto:kontakt@naturadala.pl">Skontaktuj się z obsługą</a></p>
@endsection
