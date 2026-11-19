@extends('auth.layout')

@section('title', 'Rejestr umów partnerów')

@section('content')
    <div class="admin-header-row">
        <div>
            <p class="eyebrow"><span class="eyebrow-line"></span> Administracja · rejestr</p>
            <h1 class="auth-title">Umowy<br><em>partnerów.</em></h1>
        </div>
        <a class="button button-outline button-small" href="{{ route('admin.retailers.directory') }}">Baza partnerów</a>
    </div>
    <p class="auth-description">Zarządzaj metadanymi umów przypisanymi do zatwierdzonych firm. Ten rejestr nie przechowuje plików ani treści prawnej i nie zastępuje podpisanej umowy.</p>

    @if (session('status'))
        <p class="status-message">Zmiana w rejestrze umów została zapisana.</p>
    @endif

    <aside class="retailer-demo-record" aria-labelledby="retailer-demo-heading">
        <p class="eyebrow">Przykład demonstracyjny · fikcyjne dane</p>
        <h2 id="retailer-demo-heading">Jak czytać rekord firmy i umowy</h2>
        <p><strong>Example Retail Ltd</strong> · konto partnera · właściciel wewnętrzny: Example account owner</p>
        <p><code>DEMO-0001</code> · Wholesale cooperation record · okres 01.01.2026–31.12.2026 · status: przykładowy</p>
        <p>To wyłącznie wzór pól, nie prawdziwy kontrahent ani zawarta umowa. Pola firmy nawiązują do standardowego rekordu CRM, a pola umowy do popularnych metadanych rejestrów CLM.</p>
        <ul>
            <li><a href="https://developer.salesforce.com/docs/atlas.en-us.object_reference.meta/object_reference/sforce_api_objects_account.htm" target="_blank" rel="noopener noreferrer">Salesforce: standardowe pola rekordu Account</a></li>
            <li><a href="https://www.spotdraft.com/blog/contract-metadata-reporting" target="_blank" rel="noopener noreferrer">SpotDraft: raportowanie metadanych umów</a></li>
        </ul>
    </aside>

    <section class="retailer-contract-form-section" aria-labelledby="new-contract-heading">
        <h2 id="new-contract-heading">Dodaj rekord metadanych</h2>
        @if ($retailers->isEmpty())
            <p class="notice-panel">Najpierw zatwierdź aktywną firmę, aby przypisać do niej rekord umowy.</p>
        @else
            <form class="review-form retailer-contract-form" method="POST" action="{{ route('admin.retailers.contracts.store') }}">
                @csrf
                <label for="new-retailer">Firma</label>
                <select id="new-retailer" name="retailer_profile_id" required>
                    @foreach ($retailers as $profile)
                        <option value="{{ $profile->id }}" @selected(old('retailer_profile_id') == $profile->id)>{{ $profile->legal_business_name }}</option>
                    @endforeach
                </select>
                <label for="new-reference">Numer / identyfikator</label>
                <input id="new-reference" name="reference" value="{{ old('reference') }}" maxlength="64" required>
                <label for="new-title">Tytuł rekordu</label>
                <input id="new-title" name="title" value="{{ old('title') }}" maxlength="190" required>
                <label for="new-type">Typ</label>
                <input id="new-type" name="contract_type" value="{{ old('contract_type') }}" maxlength="100" required>
                <label for="new-owner">Opiekun wewnętrzny</label>
                <select id="new-owner" name="internal_owner_id">
                    <option value="">Nieprzypisany</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected(old('internal_owner_id') == $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </select>
                <label for="new-start">Data rozpoczęcia</label>
                <input id="new-start" type="date" name="effective_date" value="{{ old('effective_date') }}">
                <label for="new-end">Data zakończenia</label>
                <input id="new-end" type="date" name="expiry_date" value="{{ old('expiry_date') }}">
                <label for="new-notice">Okres wypowiedzenia (dni)</label>
                <input id="new-notice" type="number" name="notice_period_days" min="0" max="3650" value="{{ old('notice_period_days') }}">
                <label for="new-status">Status</label>
                <select id="new-status" name="status" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', 'draft') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                @foreach (['retailer_profile_id', 'reference', 'title', 'contract_type', 'internal_owner_id', 'effective_date', 'expiry_date', 'notice_period_days', 'status'] as $field)
                    @error($field) <span class="field-error">{{ $message }}</span> @enderror
                @endforeach
                <button class="button button-dark" type="submit">Dodaj do rejestru</button>
            </form>
        @endif
    </section>

    <section class="retailer-data-section" aria-labelledby="contract-records-heading">
        <div class="retailer-section-heading"><div><p class="eyebrow"><span class="eyebrow-line"></span> Dane zapisane</p><h2 id="contract-records-heading">Rejestr metadanych</h2></div></div>
        <div class="retailer-table-wrap">
            <table class="retailer-table">
                <caption class="sr-only">Edytowalne rekordy metadanych umów</caption>
                <thead><tr><th scope="col">Firma</th><th scope="col">Numer / tytuł</th><th scope="col">Typ / opiekun</th><th scope="col">Okres / status</th><th scope="col">Zarządzanie</th></tr></thead>
                <tbody>
                    @forelse ($contracts as $contract)
                        <tr>
                            <th scope="row">{{ $contract->retailerProfile->legal_business_name }}<small>{{ $contract->retailerProfile->tax_identifier ?: 'Identyfikator niepodany' }}</small></th>
                            <td><code>{{ $contract->reference }}</code><small>{{ $contract->title }}</small></td>
                            <td>{{ $contract->contract_type }}<small>{{ $contract->internalOwner?->name ?: 'Nieprzypisany' }}</small></td>
                            <td>{{ $contract->effective_date?->format('d.m.Y') ?: '—' }} – {{ $contract->expiry_date?->format('d.m.Y') ?: '—' }}<small>{{ ucfirst($contract->status) }} · wypowiedzenie: {{ $contract->notice_period_days ?? '—' }} dni</small></td>
                            <td>
                                <details>
                                    <summary>Edytuj</summary>
                                    <form class="review-form retailer-contract-form" method="POST" action="{{ route('admin.retailers.contracts.update', $contract) }}">
                                        @csrf
                                        @method('PATCH')
                                        <label for="retailer-{{ $contract->id }}">Firma</label>
                                        <select id="retailer-{{ $contract->id }}" name="retailer_profile_id" required>
                                            @foreach ($retailers as $profile)
                                                <option value="{{ $profile->id }}" @selected($profile->id === $contract->retailer_profile_id)>{{ $profile->legal_business_name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="reference-{{ $contract->id }}">Numer</label>
                                        <input id="reference-{{ $contract->id }}" name="reference" value="{{ $contract->reference }}" maxlength="64" required>
                                        <label for="title-{{ $contract->id }}">Tytuł</label>
                                        <input id="title-{{ $contract->id }}" name="title" value="{{ $contract->title }}" maxlength="190" required>
                                        <label for="type-{{ $contract->id }}">Typ</label>
                                        <input id="type-{{ $contract->id }}" name="contract_type" value="{{ $contract->contract_type }}" maxlength="100" required>
                                        <label for="owner-{{ $contract->id }}">Opiekun wewnętrzny</label>
                                        <select id="owner-{{ $contract->id }}" name="internal_owner_id">
                                            <option value="">Nieprzypisany</option>
                                            @foreach ($owners as $owner)
                                                <option value="{{ $owner->id }}" @selected($owner->id === $contract->internal_owner_id)>{{ $owner->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="start-{{ $contract->id }}">Data rozpoczęcia</label>
                                        <input id="start-{{ $contract->id }}" type="date" name="effective_date" value="{{ $contract->effective_date?->format('Y-m-d') }}">
                                        <label for="end-{{ $contract->id }}">Data zakończenia</label>
                                        <input id="end-{{ $contract->id }}" type="date" name="expiry_date" value="{{ $contract->expiry_date?->format('Y-m-d') }}">
                                        <label for="notice-{{ $contract->id }}">Okres wypowiedzenia (dni)</label>
                                        <input id="notice-{{ $contract->id }}" type="number" name="notice_period_days" min="0" max="3650" value="{{ $contract->notice_period_days }}">
                                        <label for="status-{{ $contract->id }}">Status</label>
                                        <select id="status-{{ $contract->id }}" name="status" required>
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status }}" @selected($contract->status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                        <button class="button button-dark button-small" type="submit">Zapisz zmiany</button>
                                    </form>
                                </details>
                                <form method="POST" action="{{ route('admin.retailers.contracts.destroy', $contract) }}" onsubmit="return confirm('Usunąć ten rekord metadanych? Nie usuwa to podpisanego dokumentu.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-link" type="submit">Usuń rekord</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Brak zapisanych rekordów. Przykład powyżej jest fikcyjny i nie został dodany do bazy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $contracts->links() }}
    </section>
@endsection
