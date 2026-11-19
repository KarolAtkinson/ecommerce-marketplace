@props(['text'])

@php
    $segments = preg_split(
        '/(Produkt pasteryzowany, niedoprawiony do smaku\.?|Bulion kolagenowy w postaci naturalnego żelu \(galarety\) - suplement diety\.)/iu',
        $text,
        -1,
        PREG_SPLIT_DELIM_CAPTURE
    ) ?: [$text];
@endphp

@foreach ($segments as $segment)
    @if (preg_match('/^(Produkt pasteryzowany, niedoprawiony do smaku\.?|Bulion kolagenowy w postaci naturalnego żelu \(galarety\) - suplement diety\.)$/iu', $segment))
        <a class="faq-reference" href="{{ route('home') }}#faq"><strong>{{ $segment }}</strong></a>
    @else
        {{ $segment }}
    @endif
@endforeach
