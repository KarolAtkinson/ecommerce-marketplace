<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f6f0">
    <title>@yield('title') | Naturadała Partner</title>
    <link rel="stylesheet" href="{{ asset('styles.css') }}?v={{ filemtime(public_path('styles.css')) }}">
    <script src="{{ asset('image-fallback.js') }}?v={{ filemtime(public_path('image-fallback.js')) }}"></script>
    <script src="{{ asset('en-translations.js') }}?v={{ filemtime(public_path('en-translations.js')) }}" defer></script>
    <script src="{{ asset('cookie-consent.js') }}" defer></script>
    <script src="{{ asset('app.js') }}?v={{ filemtime(public_path('app.js')) }}" defer></script>
</head>
<body class="retailer-workspace-page">
    <a class="skip-link" href="#main">Przejdź do treści</a>
    @include('shared.header')
    <main class="retailer-main" id="main">
        <nav class="retailer-navigation" aria-label="Nawigacja partnera">
            <a href="{{ route('retailer.workspace') }}" aria-current="page">Katalog hurtowy</a>
            <a href="{{ route('basket.show') }}">Zapytania / koszyk</a>
            <a href="{{ route('account.show') }}">Moje konto</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-link" type="submit">Wyloguj się</button>
            </form>
        </nav>
        @yield('content')
    </main>
    <footer class="retailer-footer">
        <span>© {{ now()->year }} Naturadała</span>
        <button class="cookie-settings-link" type="button" data-cookie-settings>Ustawienia cookies</button>
        <span>Przestrzeń dla zatwierdzonych partnerów handlowych</span>
    </footer>
    @include('shared.basket-panel')
    @include('shared.cookie-consent')
</body>
</html>
