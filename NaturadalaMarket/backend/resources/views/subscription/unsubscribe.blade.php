@extends('auth.layout')

@section('title', 'Wypisz się z newslettera')

@section('content')
    <p class="eyebrow"><span class="eyebrow-line"></span> Newsletter</p>
    <h1 class="auth-title">Wypisz się<br><em>z newslettera.</em></h1>
    <p class="auth-description">Czy na pewno chcesz przestać otrzymywać wiadomości o nowych produktach? Utracisz też zniżkę dla subskrybentów.</p>
    <form class="auth-form" method="POST" action="{{ request()->fullUrl() }}">
        @csrf
        <button class="button button-dark auth-submit" type="submit">Tak, wypisz mnie</button>
    </form>
@endsection
