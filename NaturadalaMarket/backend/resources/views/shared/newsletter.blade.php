<section class="newsletter" id="newsletter" aria-labelledby="newsletter-title">
    <div class="newsletter-copy">
        <p class="eyebrow"><span class="eyebrow-line"></span> Newsletter</p>
        <h2 id="newsletter-title">Nowości i <em>{{ config('features.subscriber_discount_percent') }}% zniżki.</em></h2>
        <p>Zapisz się, a powiadomimy Cię e-mailem o nowych produktach. Subskrybenci mają {{ config('features.subscriber_discount_percent') }}% zniżki na wszystkie produkty po zalogowaniu na konto z tym samym adresem e-mail.</p>
    </div>
    <form class="newsletter-form" method="POST" action="{{ route('subscription.store') }}" novalidate>
        @csrf
        <label for="newsletter-email">Adres e-mail</label>
        <div class="newsletter-row">
            <input id="newsletter-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="190" required>
            <button class="button button-dark" type="submit">Zapisuję się <span aria-hidden="true">↗</span></button>
        </div>
        <input class="hp-field" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
        <label class="checkbox-label newsletter-consent">
            <input type="checkbox" name="consent" value="1" @checked(old('consent')) required>
            <span>Zgadzam się na otrzymywanie e-mailem informacji o nowych produktach Naturadała. Zgodę mogę w każdej chwili wycofać, klikając link wypisania w wiadomości.</span>
        </label>
        @if (session('subscription_status') === 'check-email')
            <p class="status-message" role="status">Sprawdź skrzynkę e-mail i kliknij link potwierdzający, aby dokończyć zapis.</p>
        @endif
        @error('email') <span class="field-error" role="alert">{{ $message }}</span> @enderror
        @error('consent') <span class="field-error" role="alert">{{ $message }}</span> @enderror
    </form>
</section>
