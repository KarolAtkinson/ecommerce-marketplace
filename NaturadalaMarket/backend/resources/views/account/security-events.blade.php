@extends('auth.layout')

@section('title', 'Historia bezpieczeństwa')

@section('content')
    <div class="admin-header-row">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Moje konto</p>
            <h1 class="auth-title">Historia<br><em>bezpieczeństwa.</em></h1>
        </div>
        <a class="text-link" href="{{ route('account.show') }}">Powrót do konta</a>
    </div>

    <p class="auth-description">Ostatnie zdarzenia związane z logowaniem i ochroną konta. Historia zawiera maksymalnie 20 zdarzeń na stronie.</p>

    @if (session('status') === 'other-sessions-revoked')
        <p class="status-message">Wylogowano inne urządzenia. To urządzenie pozostaje zalogowane.</p>
    @endif

    <form class="auth-form" method="POST" action="{{ route('account.security-events.revoke-other-sessions') }}">
        @csrf
        <p>Możesz wylogować swoje konto na innych urządzeniach. Bieżąca sesja pozostanie aktywna.</p>
        <label for="current_password">Potwierdź bieżącym hasłem</label>
        <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
        @error('current_password') <span class="field-error">{{ $message }}</span> @enderror
        <button class="button button-outline auth-submit" type="submit">Wyloguj inne urządzenia</button>
    </form>

    @php
        $eventLabels = [
            'account_registered' => 'Utworzono konto',
            'email_verified' => 'Potwierdzono adres e-mail',
            'email_change_requested' => 'Rozpoczęto zmianę adresu e-mail',
            'other_sessions_revoked' => 'Wylogowano inne urządzenia',
            'login_succeeded' => 'Udane logowanie',
            'login_failed' => 'Nieudana próba logowania',
            'login_blocked' => 'Zablokowano próbę logowania',
            'logout' => 'Wylogowano się',
            'password_changed' => 'Zmieniono hasło',
            'password_reset' => 'Zresetowano hasło',
            'mfa_enabled' => 'Włączono uwierzytelnianie dwuetapowe',
            'mfa_disabled' => 'Wyłączono uwierzytelnianie dwuetapowe',
            'mfa_recovery_codes_regenerated' => 'Wygenerowano nowe kody odzyskiwania',
            'mfa_challenge_succeeded' => 'Pomyślnie potwierdzono uwierzytelnianie dwuetapowe',
            'mfa_challenge_failed' => 'Nie udało się potwierdzić uwierzytelniania dwuetapowego',
            'retailer_application_submitted' => 'Wysłano wniosek firmy',
            'retailer_application_approved' => 'Zatwierdzono wniosek firmy',
            'retailer_application_rejected' => 'Wniosek firmy wymaga uzupełnienia',
        ];
    @endphp

    @forelse ($events as $event)
        <article class="address-card security-event-card">
            <div class="address-card-heading">
                <strong>{{ $eventLabels[$event->event_type] ?? 'Zdarzenie bezpieczeństwa konta' }}</strong>
                <time datetime="{{ $event->created_at->toIso8601String() }}">{{ $event->created_at->format('Y-m-d H:i') }}</time>
            </div>
        </article>
    @empty
        <div class="notice-panel">
            <strong>Brak zapisanych zdarzeń bezpieczeństwa.</strong>
            <p>Zdarzenia związane z logowaniem i ochroną konta pojawią się tutaj.</p>
        </div>
    @endforelse

    {{ $events->links() }}
@endsection
