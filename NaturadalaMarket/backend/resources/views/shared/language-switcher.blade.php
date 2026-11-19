<form class="language-switch-form" method="POST" action="{{ route('language.update') }}" data-language-switch>
    @csrf
    <input type="hidden" name="locale" value="{{ app()->getLocale() === 'pl' ? 'en' : 'pl' }}">
    <button class="language-switch" type="submit" lang="{{ app()->getLocale() === 'pl' ? 'en' : 'pl' }}" aria-label="{{ app()->getLocale() === 'pl' ? 'Przełącz język na angielski' : 'Switch language to Polish' }}" title="{{ app()->getLocale() === 'pl' ? 'Przełącz język na angielski' : 'Switch language to Polish' }}">
        <span aria-hidden="true">{{ app()->getLocale() === 'pl' ? 'EN' : 'PL' }}</span>
    </button>
</form>
