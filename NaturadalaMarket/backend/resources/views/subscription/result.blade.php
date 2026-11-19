@extends('auth.layout')

@section('title', $state === 'confirmed' ? 'Subskrypcja potwierdzona' : 'Wypisano z newslettera')

@section('content')
    <p class="eyebrow"><span class="eyebrow-line"></span> Newsletter</p>
    @if ($state === 'confirmed')
        <h1 class="auth-title">Dziękujemy,<br><em>zapisano.</em></h1>
        <p class="auth-description">Subskrypcja jest potwierdzona. Będziemy informować Cię o nowych produktach. Zniżka {{ config('features.subscriber_discount_percent') }}% na wszystkie produkty działa po zalogowaniu na konto z tym adresem e-mail.</p>
    @else
        <h1 class="auth-title">Wypisano<br><em>z newslettera.</em></h1>
        <p class="auth-description">Nie będziemy już wysyłać Ci wiadomości o nowych produktach, a zniżka dla subskrybentów przestaje obowiązywać.</p>
    @endif
    <a class="button button-dark" href="{{ route('products.index') }}">Przejdź do produktów <span aria-hidden="true">↗</span></a>
@endsection
