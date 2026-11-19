@extends('auth.layout')

@section('title', 'Baza partnerów')

@section('content')
    <div class="admin-header-row">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Administracja · baza danych</p>
            <h1 class="auth-title">Baza<br><em>partnerów.</em></h1>
        </div>
        <a class="button button-outline button-small" href="{{ route('admin.retailers.index') }}">Wnioski do weryfikacji</a>
    </div>
    <p class="auth-description">Wyszukuj firmy, przeglądaj statusy kont i zarządzaj dostępem do przestrzeni hurtowej. Zmiany są zapisywane w historii bezpieczeństwa.</p>

    @if (session('status') === 'retailer-access-updated')
        <p class="status-message">Dostęp partnera został zaktualizowany.</p>
    @endif

    <form class="inquiry-filters retailer-filters" method="GET" action="{{ route('admin.retailers.directory') }}">
        <label>
            <span class="sr-only">Szukaj firm</span>
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" placeholder="Szukaj firmy, NIP-u, kontaktu lub e-maila">
        </label>
        <label>
            <span class="sr-only">Status weryfikacji</span>
            <select name="status">
                <option value="">Wszystkie wnioski</option>
                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Oczekujące</option>
                <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Zatwierdzone</option>
                <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Odrzucone</option>
            </select>
        </label>
        <label>
            <span class="sr-only">Status konta</span>
            <select name="account">
                <option value="">Wszystkie konta</option>
                <option value="active" @selected(($filters['account'] ?? '') === 'active')>Aktywne</option>
                <option value="suspended" @selected(($filters['account'] ?? '') === 'suspended')>Zawieszone</option>
            </select>
        </label>
        <button class="button button-outline button-small" type="submit">Filtruj</button>
    </form>

    @forelse ($retailers as $profile)
        <article class="retailer-review-card">
            <div class="retailer-directory-heading">
                <div>
                    <p class="eyebrow">Rekord #{{ $profile->id }} · {{ $profile->created_at->format('d.m.Y') }}</p>
                    <h2>{{ $profile->legal_business_name }}</h2>
                </div>
                <span class="retailer-status-badge">{{ $profile->verification_status === 'approved' ? 'Zatwierdzona' : ($profile->verification_status === 'pending' ? 'Oczekuje' : 'Odrzucona') }} · {{ $profile->user->status === 'active' ? 'konto aktywne' : 'konto zawieszone' }}</span>
            </div>
            <dl>
                <div><dt>Kontakt</dt><dd>{{ $profile->user->name }} · <a href="mailto:{{ $profile->user->email }}">{{ $profile->user->email }}</a></dd></div>
                <div><dt>NIP / identyfikator</dt><dd>{{ $profile->tax_identifier ?: 'Nie podano' }}</dd></div>
                <div><dt>Telefon</dt><dd>{{ $profile->business_phone ?: $profile->user->phone ?: 'Nie podano' }}</dd></div>
                <div><dt>Strona internetowa</dt><dd>{{ $profile->website ?: 'Nie podano' }}</dd></div>
                <div><dt>E-mail potwierdzony</dt><dd>{{ $profile->user->hasVerifiedEmail() ? 'Tak' : 'Nie' }}</dd></div>
                <div><dt>Data zgłoszenia</dt><dd>{{ $profile->submitted_at?->format('d.m.Y H:i') ?: '—' }}</dd></div>
            </dl>
            @if ($profile->verification_status === 'approved')
                <div class="retailer-admin-actions">
                    @if ($profile->user->status === 'active')
                        <form class="review-form" method="POST" action="{{ route('admin.retailers.access.update', $profile) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="suspend">
                            <label for="suspend-reason-{{ $profile->id }}">Powód zawieszenia</label>
                            <input id="suspend-reason-{{ $profile->id }}" name="reason" required maxlength="2000">
                            <button class="button button-outline button-small" type="submit">Zawieś konto</button>
                        </form>
                        <form class="review-form" method="POST" action="{{ route('admin.retailers.access.update', $profile) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="revoke">
                            <label for="revoke-reason-{{ $profile->id }}">Powód odebrania dostępu hurtowego</label>
                            <input id="revoke-reason-{{ $profile->id }}" name="reason" required maxlength="2000">
                            <button class="button button-outline button-small" type="submit">Odbierz dostęp hurtowy</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.retailers.access.update', $profile) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="reactivate">
                            <button class="button button-dark button-small" type="submit" @disabled(! $profile->user->hasVerifiedEmail())>Przywróć konto</button>
                        </form>
                    @endif
                </div>
            @elseif ($profile->verification_status === 'pending')
                <p class="notice-panel">Aby przyznać dostęp, otwórz wniosek do weryfikacji. Zatwierdzenie wymaga aktywnego konta i potwierdzonego adresu e-mail.</p>
                <a class="text-link" href="{{ route('admin.retailers.index', ['q' => $profile->legal_business_name]) }}">Otwórz proces weryfikacji <span aria-hidden="true">↗</span></a>
            @else
                <p class="retailer-disclaimer">Ten rekord zachowano do historii. Firma może ponownie przesłać wniosek po uzupełnieniu danych.</p>
            @endif
            @error('reason') <span class="field-error">{{ $message }}</span> @enderror
        </article>
    @empty
        <div class="notice-panel"><strong>Brak firm spełniających kryteria.</strong><p>Usuń część filtrów lub sprawdź pisownię.</p></div>
    @endforelse

    {{ $retailers->links() }}
@endsection
