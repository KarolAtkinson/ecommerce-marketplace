<!doctype html>
<html lang="pl">
<body style="font-family: Arial, sans-serif; color: #2f3b31; line-height: 1.6;">
    <h2>Nowość: {{ $product->name }}</h2>
    @if ($product->short_description)
        <p>{{ $product->short_description }}</p>
    @endif
    @if ($product->image_url)
        <p><img src="{{ $product->image_url }}" alt="{{ $product->name }}" width="280"></p>
    @endif
    <p><a href="{{ $productUrl }}" style="display:inline-block;padding:12px 22px;background:#37483a;color:#fff;text-decoration:none;">Zobacz produkt</a></p>
    <p>Jako subskrybent masz {{ $discount }}% zniżki po zalogowaniu na konto z tym adresem e-mail.</p>
    <hr>
    <p style="font-size:12px;color:#77796f;">Otrzymujesz tę wiadomość, bo zapisałeś(-aś) się do newslettera Naturadała. <a href="{{ $unsubscribeUrl }}">Wypisz się</a>.</p>
</body>
</html>
