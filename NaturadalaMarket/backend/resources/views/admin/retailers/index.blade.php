@extends('auth.layout')

@section('title', 'Weryfikacja firm')

@section('content')
    <h1 class="auth-title">Weryfikacja<br><em>partnerów.</em></h1>
    <p class="auth-description">Dostęp administracyjny jest chroniony rolą administratora i aktywnym uwierzytelnianiem dwuetapowym.</p>
    @if (session('status') === 'retailer-application-reviewed')
        <p class="status-message">Decyzja została zapisana w historii bezpieczeństwa konta.</p>
    @endif

    <form class="inquiry-filters retailer-filters" method="GET" action="{{ route('admin.retailers.index') }}">
        <label>
            <span class="sr-only">Szukaj wniosków</span>
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" placeholder="Szukaj firmy, NIP-u, osoby lub e-maila">
        </label>
        <button class="button button-outline button-small" type="submit">Szukaj</button>
        @if (($filters['q'] ?? '') !== '')
            <a class="text-link" href="{{ route('admin.retailers.index') }}">Wyczyść</a>
        @endif
    </form>

    @forelse ($applications as $profile)
        <article class="retailer-review-card">
            <p class="eyebrow">Wniosek · {{ $profile->submitted_at?->format('d.m.Y H:i') ?? 'brak daty' }}</p>
            <h2>{{ $profile->legal_business_name }}</h2>
            <dl>
                <div><dt>Osoba kontaktowa</dt><dd>{{ $profile->user->name }}</dd></div>
                <div><dt>E-mail</dt><dd>{{ $profile->user->email }}</dd></div>
                <div><dt>Adres potwierdzony</dt><dd>{{ $profile->user->hasVerifiedEmail() ? 'Tak' : 'Nie' }}</dd></div>
                <div><dt>NIP</dt><dd>{{ $profile->tax_identifier ?: 'Nie podano' }}</dd></div>
                <div><dt>Telefon</dt><dd>{{ $profile->business_phone ?: $profile->user->phone ?: 'Nie podano' }}</dd></div>
                @if ($profile->website)<div><dt>Strona</dt><dd><a href="{{ $profile->website }}" target="_blank" rel="noreferrer">{{ $profile->website }}</a></dd></div>@endif
            </dl>
            @if ($profile->user->status !== 'active' || ! $profile->user->hasVerifiedEmail())
                <p class="notice-panel">Nie można podjąć decyzji: konto jest nieaktywne lub e-mail nie został potwierdzony.</p>
            @else
                <form class="review-form" method="POST" action="{{ route('admin.retailers.update', $profile) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="filter_q" value="{{ $filters['q'] ?? '' }}">
                    <label for="notes-{{ $profile->id }}">Notatka wewnętrzna / powód decyzji</label>
                    <textarea id="notes-{{ $profile->id }}" name="review_notes" rows="3" maxlength="2000">{{ old('review_notes') }}</textarea>
                    @error('review_notes') <span class="field-error">{{ $message }}</span> @enderror
                    <div class="review-actions">
                        <button class="button button-dark" name="decision" value="approve" type="submit">Zatwierdź firmę</button>
                        <button class="button button-outline" name="decision" value="reject" type="submit">Odrzuć wniosek</button>
                    </div>
                </form>
            @endif
        </article>
    @empty
        <div class="notice-panel">
            @if (($filters['q'] ?? '') !== '')
                <strong>Nie znaleziono pasujących wniosków.</strong>
                <p>Sprawdź pisownię lub wyczyść wyszukiwanie.</p>
            @else
                <strong>Brak wniosków do rozpatrzenia.</strong>
                <p>Nowe wnioski zweryfikowanych adresem e-mail firm pojawią się tutaj.</p>
            @endif
        </div>
    @endforelse

    {{ $applications->links() }}
@endsection
