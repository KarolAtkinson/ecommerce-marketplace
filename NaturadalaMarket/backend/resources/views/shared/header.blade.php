@php
  $currentPage = $currentPage ?? null;
  $hideNavigation = $hideNavigation ?? false;
@endphp

<header class="site-header">
  <div class="brand-language-group">
    <a class="brand" href="{{ route('home') }}" aria-label="Naturadała — strona główna">
      <img src="{{ asset('images/logo-horizontal.png') }}" alt="Naturadała">
    </a>
    <button class="menu-toggle" type="button" aria-label="Menu" aria-expanded="false" aria-controls="primary-navigation">
      <img class="menu-toggle-logo" src="{{ asset('images/logo-horizontal.png') }}" alt="">
    </button>
    @include('shared.language-switcher')
  </div>

  @if (!$hideNavigation)
    <nav class="primary-navigation" id="primary-navigation" aria-label="Nawigacja główna">
      @if ($currentPage !== 'home')
        <a href="{{ route('home') }}">Strona główna</a>
      @endif
      <a href="{{ route('products.index') }}" @if ($currentPage === 'products') aria-current="page" @endif>Produkty</a>
      <a href="{{ route('home') }}#dla-kogo">Dla kogo</a>
      <a href="{{ route('home') }}#faq">Pytania i odpowiedzi</a>
      <a href="{{ route('home') }}#o-nas">O Naturadale</a>
      <a href="{{ route('home') }}#kontakt">Kontakt</a>
    </nav>
  @endif

  <div class="header-actions">
    @guest
      <a class="account-link" href="{{ route('login') }}">Zaloguj się</a>
      @if (config('marketplace.registration_enabled'))
        <a class="button button-small button-outline" href="{{ route('register') }}">Zarejestruj się</a>
      @endif
    @else
      <a class="account-link" href="{{ route('account.show') }}">Moje konto</a>
      @if (auth()->user()->isApprovedRetailer() && (! config('features.retailer_maintenance') || auth()->user()->hasRole('admin')))
        <a class="account-link" href="{{ route('retailer.workspace') }}">Przestrzeń partnera</a>
      @endif
      @if (auth()->user()->hasVerifiedEmail())
        <a class="account-link" href="{{ route('wishlist.index') }}">Ulubione</a>
      @endif
    @endguest
    <a class="button button-small button-dark" href="{{ route('products.index') }}">Oferta <span aria-hidden="true">↗</span></a>
    @include('shared.basket-button')
  </div>
</header>
