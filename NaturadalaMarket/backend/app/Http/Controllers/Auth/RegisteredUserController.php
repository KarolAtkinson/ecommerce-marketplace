<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ConsentRecord;
use App\Models\Role;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'registrationEnabled' => (bool) config('marketplace.registration_enabled'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(config('marketplace.registration_enabled'), 503, 'Rejestracja zostanie uruchomiona po opublikowaniu regulaminu i polityki prywatności.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'string', 'email:rfc', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'account_type' => ['required', 'in:customer,retailer'],
            'business_name' => ['required_if:account_type,retailer', 'nullable', 'string', 'max:190'],
            'tax_identifier' => ['nullable', 'string', 'max:32', 'unique:retailer_profiles,tax_identifier'],
            'business_phone' => ['nullable', 'string', 'max:32'],
            'terms_accepted' => ['accepted'],
            'marketing_consent' => ['sometimes', 'accepted'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($validated, $request): User {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => mb_strtolower($validated['email']),
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
                'status' => 'active',
            ]);

            $customerRole = Role::query()->where('slug', 'customer')->firstOrFail();
            $user->roles()->attach($customerRole);

            if ($validated['account_type'] === 'retailer') {
                $user->retailerProfile()->create([
                    'legal_business_name' => $validated['business_name'],
                    'tax_identifier' => $validated['tax_identifier'] ?? null,
                    'business_phone' => $validated['business_phone'] ?? null,
                ]);
            }

            $now = now();
            $records = [
                [
                    'user_id' => $user->id,
                    'consent_type' => 'terms',
                    'document_version' => config('marketplace.terms_version'),
                    'accepted' => true,
                    'recorded_at' => $now,
                ],
                [
                    'user_id' => $user->id,
                    'consent_type' => 'privacy',
                    'document_version' => config('marketplace.privacy_version'),
                    'accepted' => true,
                    'recorded_at' => $now,
                ],
            ];

            if ($request->boolean('marketing_consent')) {
                $records[] = [
                    'user_id' => $user->id,
                    'consent_type' => 'marketing',
                    'document_version' => config('marketplace.privacy_version'),
                    'accepted' => true,
                    'recorded_at' => $now,
                ];
            }

            ConsentRecord::query()->insert($records);

            SecurityEvent::query()->create([
                'user_id' => $user->id,
                'event_type' => 'account_registered',
                'ip_address' => $request->ip(),
            ]);

            return $user;
        });

        event(new Registered($user));
        auth()->login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }
}
