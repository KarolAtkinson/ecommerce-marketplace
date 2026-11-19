<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f6f0">
    <meta name="description" content="@yield('description', 'Poznaj produkty Naturadała dla klientów i zatwierdzonych partnerów hurtowych.')">
    <title>@yield('title', 'Produkty') | Naturadała</title>
    <link rel="preconnect" href="https://naturadala.pl">
    <link rel="stylesheet" href="{{ asset('styles.css') }}?v={{ filemtime(public_path('styles.css')) }}">
    <script src="{{ asset('image-fallback.js') }}?v={{ filemtime(public_path('image-fallback.js')) }}"></script>
    <script src="{{ asset('en-translations.js') }}?v={{ filemtime(public_path('en-translations.js')) }}" defer></script>
    <script src="{{ asset('cookie-consent.js') }}" defer></script>
    <script src="{{ asset('app.js') }}?v={{ filemtime(public_path('app.js')) }}" defer></script>
</head>
<body class="marketplace-page">
    <a class="skip-link" href="#main">Przejdź do treści</a>
    @include('shared.announcement')
    @include('shared.header', ['currentPage' => 'products'])
    <main id="main">
        @yield('content')
    </main>
    @include('shared.newsletter')
    <footer class="site-footer">
        <a class="footer-brand" href="{{ route('home') }}" aria-label="Naturadała — strona główna">
            <img src="{{ asset('images/logo-footer.png') }}" alt="Naturadała">
        </a>
        <p>Codzienne wsparcie organizmu<br>tak jak natura dała.</p>
        <nav aria-label="Nawigacja w stopce">
            <a href="{{ route('products.index') }}">Produkty</a>
            <a href="{{ route('home') }}#dla-kogo">Dla kogo</a>
            <a href="{{ route('home') }}#faq">Pytania i odpowiedzi</a>
            <a href="{{ route('home') }}#o-nas">O nas</a>
            <a href="mailto:kontakt@naturadala.pl">Kontakt</a>
        </nav>
        <span class="copyright">© {{ now()->year }} Naturadała <button class="cookie-settings-link" type="button" data-cookie-settings>Ustawienia cookies</button></span>
    </footer>
    @include('shared.basket-panel')
    @include('shared.cookie-consent')
</body>
</html>
