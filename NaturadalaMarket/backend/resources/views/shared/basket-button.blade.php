<button class="cart-trigger basket-button" type="button" aria-haspopup="dialog" aria-controls="basket-popup" aria-label="Otwórz koszyk">
    <svg class="basket-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M6 8h12l-1 12H7L6 8Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M9 8V6a3 3 0 0 1 6 0v2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
    <span class="basket-label">Koszyk</span>
    <span class="cart-count" aria-live="polite">{{ $accountBasketCount ?? 0 }}</span>
</button>
