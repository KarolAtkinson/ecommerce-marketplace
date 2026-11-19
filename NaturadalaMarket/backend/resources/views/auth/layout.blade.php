<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f6f0">
    <title>@yield('title') | Naturadała</title>
    <link rel="stylesheet" href="{{ asset('styles.css') }}?v={{ filemtime(public_path('styles.css')) }}">
    <script src="{{ asset('image-fallback.js') }}?v={{ filemtime(public_path('image-fallback.js')) }}"></script>
    <script src="{{ asset('en-translations.js') }}?v={{ filemtime(public_path('en-translations.js')) }}" defer></script>
    <script src="{{ asset('cookie-consent.js') }}" defer></script>
    <script src="{{ asset('app.js') }}?v={{ filemtime(public_path('app.js')) }}" defer></script>
</head>
<body class="auth-page">
    <a class="skip-link" href="#main">Przejdź do treści</a>
    @include('shared.header')
    <main class="auth-main" id="main">
        <section class="auth-card">
            <p class="eyebrow"><span class="eyebrow-line"></span> Naturadała · konto</p>
            @if ($errors->any())
                <div class="form-errors" role="alert">
                    <p>Sprawdź dane w formularzu:</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </section>
    </main>
    <footer class="auth-footer">
        <a href="mailto:kontakt@naturadala.pl">Potrzebujesz pomocy? Napisz do nas</a>
        <span>© {{ now()->year }} Naturadała</span>
        <button class="cookie-settings-link" type="button" data-cookie-settings>Ustawienia cookies</button>
    </footer>
    @include('shared.basket-panel')
    @include('shared.cookie-consent')
</body>
</html>
