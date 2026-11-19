@extends('auth.layout')

@section('title', 'Potwierdź adres e-mail')

@section('content')
    <h1 class="auth-title">Jeszcze<br><em>jeden krok.</em></h1>
    <p class="auth-description">Wysłaliśmy wiadomość z linkiem weryfikacyjnym na adres <strong>{{ auth()->user()->email }}</strong>. Otwórz link, aby potwierdzić adres     i uzyskać dostęp do konta. Link jest ważny przez {{ config('auth.verification.expire') }} minut — po tym czasie wyślij go ponownie.</p>
    @if (session('status') === 'verification-link-sent')
        <p class="status-message">Wysłaliśmy nowy link weryfikacyjny.</p>
    @endif
    @if (session('status') === 'email-change-verification-sent')
        <p class="status-message">Wysłaliśmy link weryfikacyjny na nowy adres e-mail. Potwierdź go, aby ponownie uzyskać dostęp do konta.</p>
    @endif
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="button button-dark auth-submit" type="submit">Wyślij link ponownie <span aria-hidden="true">↗</span></button>
    </form>
    <form class="logout-form" method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="text-link" type="submit">Wyloguj się</button>
    </form>
@endsection
