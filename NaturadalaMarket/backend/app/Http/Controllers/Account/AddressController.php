<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.addresses.index', [
            'addresses' => $request->user()->addresses()->orderByDesc('is_default_shipping')->orderBy('city')->get(),
        ]);
    }

    public function create(): View
    {
        return view('account.addresses.form', ['address' => new Address]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAddress($request);

        DB::transaction(function () use ($request, $validated): void {
            $this->clearOtherDefaults($request, $validated);
            $request->user()->addresses()->create($validated);
        });

        return redirect()->route('addresses.index')->with('status', 'address-saved');
    }

    public function edit(Request $request, Address $address): View
    {
        $address = $request->user()->addresses()->findOrFail($address->id);

        return view('account.addresses.form', compact('address'));
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        $address = $request->user()->addresses()->findOrFail($address->id);
        $validated = $this->validateAddress($request);

        DB::transaction(function () use ($request, $address, $validated): void {
            $this->clearOtherDefaults($request, $validated, $address);
            $address->update($validated);
        });

        return redirect()->route('addresses.index')->with('status', 'address-saved');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $request->user()->addresses()->findOrFail($address->id)->delete();

        return redirect()->route('addresses.index')->with('status', 'address-deleted');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAddress(Request $request): array
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:80'],
            'recipient_name' => ['required', 'string', 'max:190'],
            'company_name' => ['nullable', 'string', 'max:190'],
            'street_line_1' => ['required', 'string', 'max:190'],
            'street_line_2' => ['nullable', 'string', 'max:190'],
            'postal_code' => ['required', 'string', 'max:24'],
            'city' => ['required', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'size:2', 'alpha:ascii'],
            'phone' => ['nullable', 'string', 'max:32'],
            'is_default_billing' => ['sometimes', 'boolean'],
            'is_default_shipping' => ['sometimes', 'boolean'],
        ]);

        $validated['country_code'] = strtoupper($validated['country_code']);
        $validated['is_default_billing'] = $request->boolean('is_default_billing');
        $validated['is_default_shipping'] = $request->boolean('is_default_shipping');

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function clearOtherDefaults(Request $request, array $validated, ?Address $address = null): void
    {
        $addresses = $request->user()->addresses();
        if ($address !== null) {
            $addresses->whereKeyNot($address->id);
        }

        if ($validated['is_default_billing']) {
            (clone $addresses)->where('is_default_billing', true)->update(['is_default_billing' => false]);
        }

        if ($validated['is_default_shipping']) {
            (clone $addresses)->where('is_default_shipping', true)->update(['is_default_shipping' => false]);
        }
    }
}
