<h1>Nowe zapytanie hurtowe #{{ $inquiry->getKey() }}</h1>

<p><strong>Kontakt:</strong> {{ $inquiry->name }} &lt;{{ $inquiry->email }}&gt;</p>
@if ($inquiry->company_name)
    <p><strong>Firma:</strong> {{ $inquiry->company_name }}</p>
@endif
@if ($inquiry->phone)
    <p><strong>Telefon:</strong> {{ $inquiry->phone }}</p>
@endif

@if ($inquiry->products)
    <h2>Produkty</h2>
    <ul>
        @foreach ($inquiry->products as $product)
            <li>{{ $product['name'] ?? 'Produkt' }} — {{ $product['quantity'] ?? 0 }} szt.</li>
        @endforeach
    </ul>
@endif

@if ($inquiry->message)
    <h2>Wiadomość</h2>
    <p>{!! nl2br(e($inquiry->message)) !!}</p>
@endif

<p><a href="{{ route('admin.inquiries.index') }}">Otwórz skrzynkę zapytań</a></p>
