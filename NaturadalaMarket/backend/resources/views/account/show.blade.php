@extends('auth.layout')

@section('title', 'Moje konto')

@section('content')
    <h1 class="auth-title">Witaj,<br><em>{{ $user->name }}.</em></h1>
    @if (session('status') === 'email-verified')
        <p class="status-message">Adres e-mail został potwierdzony.</p>
    @elseif (session('status') === 'profile-updated')
        <p class="status-message">Dane konta zostały zaktualizowane.</p>
    @elseif (session('status') === 'password-updated')
        <p class="status-message">Hasło zostało zmienione. Tokeny „zapamiętaj mnie” zostały unieważnione.</p>
    @endif
    <p class="auth-description">Zarządzaj dostępem do katalogu i ustawieniami konta. Zamówienia online zostaną dodane w kolejnym etapie.</p>
    @if (session('status') === 'retailer-application-submitted')
        <p class="status-message">Wniosek firmy zapisano. Zespół Naturadała zweryfikuje dane przed przyznaniem dostępu hurtowego.</p>
    @endif
    <div class="account-summary">
        <div>
            <span>Adres e-mail</span>
            <strong>{{ $user->email }}</strong>
        </div>
        <div>
            <span>Status konta</span>
            <strong>Aktywne</strong>
        </div>
        <div>
            <span>Dodatkowa ochrona</span>
            <strong>{{ $user->two_factor_confirmed_at ? 'Włączona' : 'Wyłączona' }}</strong>
        </div>
        @if ($user->retailerProfile)
            <div>
                <span>Weryfikacja firmy</span>
                <strong>
                    @if ($user->retailerProfile->verification_status === 'approved')
                        Zatwierdzona
                    @elseif ($user->retailerProfile->verification_status === 'rejected')
                        Wymaga uzupełnienia
                    @else
                        Oczekuje na weryfikację
                    @endif
                </strong>
            </div>
        @endif
    </div>
    @if ($user->retailerProfile?->verification_status === 'pending')
        <p class="notice-panel">Wniosek firmy nie daje jeszcze dostępu do cen hurtowych. Oczekuje na weryfikację.</p>
    @elseif ($user->retailerProfile?->verification_status === 'rejected')
        <div class="notice-panel">
            <strong>Wniosek wymaga uzupełnienia.</strong>
            @if ($user->retailerProfile->review_notes)
                <p>{{ $user->retailerProfile->review_notes }}</p>
            @endif
            <a class="text-link" href="{{ route('retailer.application') }}">Popraw i wyślij wniosek ponownie <span aria-hidden="true">↗</span></a>
        </div>
    @endif
    <div class="account-action-links">
        <a class="button button-outline" href="{{ route('account.profile.edit') }}">Dane konta <span aria-hidden="true">↗</span></a>
        <a class="button button-outline" href="{{ route('account.password.edit') }}">Zmień hasło <span aria-hidden="true">↗</span></a>
        <a class="button button-outline" href="{{ route('account.security-events.index') }}">Historia bezpieczeństwa <span aria-hidden="true">↗</span></a>
        <a class="button button-outline" href="{{ route('products.index') }}">Przeglądaj produkty <span aria-hidden="true">↗</span></a>
        <a class="button button-outline" href="{{ route('wishlist.index') }}">Moje ulubione <span aria-hidden="true">↗</span></a>
        <a class="button button-outline" href="{{ route('basket.show') }}">Mój koszyk <span aria-hidden="true">↗</span></a>
        <a class="button button-outline" href="{{ route('addresses.index') }}">Moje adresy <span aria-hidden="true">↗</span></a>
        @if (config('features.retailer_maintenance') && ! $user->hasRole('admin'))
            <span class="button button-outline is-disabled" aria-disabled="true">Strefa partnera: przerwa techniczna</span>
        @elseif (! $user->retailerProfile || $user->retailerProfile->verification_status !== 'approved')
            <a class="button button-outline" href="{{ route('retailer.application') }}">{{ $user->retailerProfile?->verification_status === 'pending' ? 'Zobacz wniosek firmy' : 'Zgłoś firmę do weryfikacji' }} <span aria-hidden="true">↗</span></a>
        @else
            <a class="button button-dark" href="{{ route('retailer.workspace') }}">Otwórz przestrzeń partnera <span aria-hidden="true">↗</span></a>
        @endif
        @if ($user->hasRole('admin') && $user->two_factor_confirmed_at)
            <a class="button button-dark" href="{{ route('admin.retailers.index') }}">Wnioski firm <span aria-hidden="true">↗</span></a>
            <a class="button button-outline" href="{{ route('admin.retailers.directory') }}">Baza partnerów <span aria-hidden="true">↗</span></a>
            <a class="button button-outline" href="{{ route('admin.retailers.contracts.index') }}">Rejestr umów <span aria-hidden="true">↗</span></a>
        @endif
    </div>
    <a class="button button-outline auth-submit" href="{{ route('mfa.show') }}">Ustawienia uwierzytelniania <span aria-hidden="true">↗</span></a>
    <form class="logout-form" method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="button button-dark auth-submit" type="submit">Wyloguj się</button>
    </form>
@endsection
