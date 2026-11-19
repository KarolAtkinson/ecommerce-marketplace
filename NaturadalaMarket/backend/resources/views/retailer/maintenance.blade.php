@extends('auth.layout')

@section('title', 'Przerwa techniczna')

@section('content')
    <p class="eyebrow"><span class="eyebrow-line"></span> Strefa partnera</p>
    <h1 class="auth-title">Przerwa<br><em>techniczna.</em></h1>
    <div class="notice-panel" role="status">
        <strong>Strefa dla partnerów handlowych jest chwilowo niedostępna.</strong>
        <p>Pracujemy nad jej rozwojem i wrócimy z nią wkrótce. Ceny hurtowe oraz wnioski firm są na ten czas wstrzymane.</p>
        <p>Pytania dotyczące współpracy: <a class="text-link" href="mailto:kontakt@naturadala.pl">kontakt@naturadala.pl</a></p>
        <a class="button button-dark" href="{{ route('products.index') }}">Przejdź do produktów <span aria-hidden="true">↗</span></a>
    </div>
@endsection
