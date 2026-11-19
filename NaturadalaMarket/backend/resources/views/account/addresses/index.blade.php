@extends('auth.layout')

@section('title', 'Moje adresy')

@section('content')
    <div class="admin-header-row">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Moje konto</p>
            <h1 class="auth-title">Zapisane<br><em>adresy.</em></h1>
        </div>
        <a class="button button-dark" href="{{ route('addresses.create') }}">Dodaj adres</a>
    </div>

    @if (session('status') === 'address-saved')
        <p class="status-message">Adres został zapisany.</p>
    @elseif (session('status') === 'address-deleted')
        <p class="status-message">Adres został usunięty.</p>
    @endif

    <p class="auth-description">Zarządzaj zapisanymi adresami. Zakupy online nie są jeszcze dostępne.</p>

    @forelse ($addresses as $address)
        <article class="address-card">
            <div class="address-card-heading">
                <div>
                    <h2>{{ $address->label ?: $address->recipient_name }}</h2>
                    <div class="address-badges">
                        @if ($address->is_default_billing)<span class="audience-badge">Domyślny rozliczeniowy</span>@endif
                        @if ($address->is_default_shipping)<span class="audience-badge">Domyślny dostawy</span>@endif
                    </div>
                </div>
                <div class="admin-actions">
                    <a class="button button-outline button-small" href="{{ route('addresses.edit', $address) }}">Edytuj</a>
                    <form method="POST" action="{{ route('addresses.destroy', $address) }}" onsubmit="return confirm('Usunąć zapisany adres?');">
                        @csrf
                        @method('DELETE')
                        <button class="button button-danger button-small" type="submit">Usuń</button>
                    </form>
                </div>
            </div>
            <p>
                <strong>{{ $address->recipient_name }}</strong>
                @if ($address->company_name)<br>{{ $address->company_name }}@endif
                <br>{{ $address->street_line_1 }}
                @if ($address->street_line_2)<br>{{ $address->street_line_2 }}@endif
                <br>{{ $address->postal_code }} {{ $address->city }}
                @if ($address->region)<br>{{ $address->region }}@endif
                <br>{{ $address->country_code }}
                @if ($address->phone)<br>{{ $address->phone }}@endif
            </p>
        </article>
    @empty
        <div class="notice-panel">
            <strong>Nie masz jeszcze zapisanych adresów.</strong>
            <p>Dodaj adres, aby mieć go pod ręką przy przyszłych zamówieniach.</p>
            <a class="button button-dark" href="{{ route('addresses.create') }}">Dodaj adres</a>
        </div>
    @endforelse

    <a class="text-link" href="{{ route('account.show') }}">Powrót do konta</a>
@endsection
