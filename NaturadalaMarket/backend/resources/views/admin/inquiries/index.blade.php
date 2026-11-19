@extends('auth.layout')

@section('title', 'Zapytania hurtowe')

@section('content')
    <div class="admin-header-row">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Administracja</p>
            <h1 class="auth-title">Zapytania<br><em>hurtowe.</em></h1>
        </div>
    </div>

    @if (session('status') === 'inquiry-updated')
        <p class="status-message">Status zapytania został zaktualizowany.</p>
    @endif

    <form class="inquiry-filters" method="GET" action="{{ route('admin.inquiries.index') }}">
        <label>
            <span class="sr-only">Szukaj zapytań</span>
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" placeholder="Szukaj osoby, firmy lub e-maila">
        </label>
        <label>
            <span class="sr-only">Filtruj według statusu</span>
            <select name="status">
                <option value="">Wszystkie statusy</option>
                <option value="new" @selected(($filters['status'] ?? '') === 'new')>Nowe</option>
                <option value="contacted" @selected(($filters['status'] ?? '') === 'contacted')>Kontaktowane</option>
                <option value="closed" @selected(($filters['status'] ?? '') === 'closed')>Zamknięte</option>
            </select>
        </label>
        <button class="button button-outline button-small" type="submit">Filtruj</button>
        @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? '') !== '')
            <a class="text-link" href="{{ route('admin.inquiries.index') }}">Wyczyść</a>
        @endif
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kontakt</th>
                    <th>Firma</th>
                    <th>Produkty</th>
                    <th>Wiadomość</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inquiries as $inquiry)
                    @php
                        $products = $inquiry->products;
                        if (is_string($products)) {
                            $products = json_decode($products, true) ?: [];
                        }
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $inquiry->name }}</strong><br>
                            <a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a>
                            @if ($inquiry->phone)
                                <br><a href="tel:{{ $inquiry->phone }}">{{ $inquiry->phone }}</a>
                            @endif
                        </td>
                        <td>{{ $inquiry->company_name ?: '—' }}</td>
                        <td>
                            @if (! empty($products))
                                @foreach ($products as $product)
                                    <div>{{ $product['name'] ?? 'Produkt' }} · {{ $product['quantity'] ?? 0 }} szt.</div>
                                @endforeach
                            @else
                                <span>—</span>
                            @endif
                        </td>
                        <td class="inquiry-message">
                            @if ($inquiry->message)
                                {{ $inquiry->message }}
                            @else
                                <span>Brak dodatkowej wiadomości</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="filter_status" value="{{ $filters['status'] ?? '' }}">
                                <input type="hidden" name="filter_q" value="{{ $filters['q'] ?? '' }}">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="new" @selected($inquiry->status === 'new')>Nowe</option>
                                    <option value="contacted" @selected($inquiry->status === 'contacted')>Kontaktowane</option>
                                    <option value="closed" @selected($inquiry->status === 'closed')>Zamknięte</option>
                                </select>
                            </form>
                        </td>
                        <td>{{ $inquiry->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Brak zapytań hurtowych.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $inquiries->links() }}
@endsection
