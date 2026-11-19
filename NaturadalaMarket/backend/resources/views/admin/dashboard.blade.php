@extends('auth.layout')

@section('title', 'Panel administracyjny')

@section('content')
    <div class="admin-header-row">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Administracja</p>
            <h1 class="auth-title">Panel<br><em>administracyjny.</em></h1>
        </div>
    </div>

    <div class="admin-dashboard-grid">
        <article class="stat-card">
            <span class="stat-label">Produkty</span>
            <strong>{{ $productsCount }}</strong>
            <small>{{ $activeProductsCount }} aktywnych</small>
        </article>
        <article class="stat-card">
            <span class="stat-label">Sprzedawcy</span>
            <strong>{{ $retailersCount }}</strong>
            <small>{{ $approvedApplicationsCount }} zatwierdzonych</small>
        </article>
        <article class="stat-card stat-card-warning">
            <span class="stat-label">Wnioski</span>
            <strong>{{ $pendingApplicationsCount }}</strong>
            <small>oczekujących na decyzję</small>
        </article>
        <article class="stat-card">
            <span class="stat-label">Zapytania</span>
            <strong>{{ $newInquiriesCount }}</strong>
            <small>nowych zapytań hurtowych</small>
        </article>
    </div>

    <div class="admin-dashboard-actions">
        <a class="button button-dark" href="{{ route('admin.products.index') }}">Zarządzaj katalogiem</a>
        <a class="button button-outline" href="{{ route('admin.retailers.index') }}">Weryfikacja firm</a>
        <a class="button button-outline" href="{{ route('admin.retailers.directory') }}">Baza partnerów</a>
        <a class="button button-outline" href="{{ route('admin.retailers.contracts.index') }}">Rejestr umów</a>
        <a class="button button-outline" href="{{ route('admin.inquiries.index') }}">Zapytania hurtowe</a>
    </div>

    @if ($recentInquiries->isNotEmpty())
        <section class="dashboard-inquiries" aria-labelledby="dashboard-inquiries-title">
            <div class="dashboard-section-heading">
                <div>
                    <p class="eyebrow"><span class="eyebrow-line"></span> Wymagają kontaktu</p>
                    <h2 id="dashboard-inquiries-title">Nowe zapytania</h2>
                </div>
                <a class="text-link" href="{{ route('admin.inquiries.index', ['status' => 'new']) }}">Otwórz skrzynkę <span aria-hidden="true">↗</span></a>
            </div>
            <div class="dashboard-inquiry-list">
                @foreach ($recentInquiries as $inquiry)
                    <article class="dashboard-inquiry">
                        <div>
                            <strong>{{ $inquiry->name }}</strong>
                            <span>{{ $inquiry->company_name ?: $inquiry->email }}</span>
                        </div>
                        <span>{{ $inquiry->created_at->format('d.m.Y H:i') }}</span>
                        <a href="mailto:{{ $inquiry->email }}" aria-label="Napisz do {{ $inquiry->name }}">{{ $inquiry->email }}</a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($pendingApplicationsCount > 0 || $newInquiriesCount > 0)
        <div class="notice-panel">
            <strong>Do rozpatrzenia są nowe zgłoszenia.</strong>
            <p>Sprawdź listę wniosków firmowych i zapytań hurtowych przed udostępnieniem cen lub dalszą obsługą klienta.</p>
        </div>
    @endif
@endsection
