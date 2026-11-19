@extends('auth.layout')

@section('title', 'Weryfikacja firmy')

@section('content')
    <h1 class="auth-title">Współpracuj<br><em>z Naturadałą.</em></h1>
    @if ($profile?->verification_status === 'approved')
        <p class="auth-description">Ta firma została już zweryfikowana. Konto ma dostęp do warunków partnerskich.</p>
    @elseif ($profile?->verification_status === 'pending')
        <p class="auth-description">Otrzymaliśmy wniosek z {{ $profile->legal_business_name }}. Weryfikacja jest w toku. Ceny hurtowe pojawią się po zatwierdzeniu firmy.</p>
        <div class="account-summary">
            <div><span>Nazwa firmy</span><strong>{{ $profile->legal_business_name }}</strong></div>
            <div><span>Status</span><strong>Oczekuje na weryfikację</strong></div>
            @if ($profile->submitted_at)
                <div><span>Wysłano</span><strong>{{ $profile->submitted_at->format('d.m.Y') }}</strong></div>
            @endif
        </div>
    @else
        @if ($profile?->verification_status === 'rejected')
            <div class="notice-panel">
                <strong>Poprzedni wniosek wymagał uzupełnienia.</strong>
                @if ($profile->review_notes)
                    <p>{{ $profile->review_notes }}</p>
                @endif
                @if ($profile->reviewed_at)
                    <p>Rozpatrzono: {{ $profile->reviewed_at->format('d.m.Y') }}</p>
                @endif
            </div>
        @endif
        <p class="auth-description">Podaj dane firmy, aby złożyć wniosek o dostęp do warunków hurtowych. Wniosek przejdzie ręczną weryfikację i nie przyznaje cen partnerskich automatycznie.</p>
        <form class="auth-form" method="POST" action="{{ route('retailer.application.store') }}">
            @csrf
            <label for="legal_business_name">Pełna nazwa firmy</label>
            <input id="legal_business_name" name="legal_business_name" type="text" value="{{ old('legal_business_name', $profile?->legal_business_name) }}" autocomplete="organization" maxlength="190" required>
            @error('legal_business_name') <span class="field-error">{{ $message }}</span> @enderror

            <label for="tax_identifier">NIP <span class="field-optional">(opcjonalnie)</span></label>
            <input id="tax_identifier" name="tax_identifier" type="text" value="{{ old('tax_identifier', $profile?->tax_identifier) }}" autocomplete="off" maxlength="32">
            @error('tax_identifier') <span class="field-error">{{ $message }}</span> @enderror

            <label for="business_phone">Telefon firmowy <span class="field-optional">(opcjonalnie)</span></label>
            <input id="business_phone" name="business_phone" type="tel" value="{{ old('business_phone', $profile?->business_phone) }}" maxlength="32">

            <label for="website">Strona internetowa firmy <span class="field-optional">(opcjonalnie)</span></label>
            <input id="website" name="website" type="url" value="{{ old('website', $profile?->website) }}" autocomplete="url" maxlength="255">
            @error('website') <span class="field-error">{{ $message }}</span> @enderror

            <button class="button button-dark auth-submit" type="submit">{{ $profile ? 'Wyślij wniosek ponownie' : 'Wyślij do weryfikacji' }} <span aria-hidden="true">↗</span></button>
        </form>
    @endif
    <p class="auth-switch"><a href="{{ route('account.show') }}">Wróć do konta</a></p>
@endsection
