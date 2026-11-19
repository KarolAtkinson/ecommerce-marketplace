<?php

namespace App\Http\Controllers;

use App\Models\RetailerProfile;
use App\Models\SecurityEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RetailerApplicationController extends Controller
{
    public function create(Request $request): View
    {
        return view('retailer.application', [
            'profile' => $request->user()->retailerProfile,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user()->fresh();
        $existingProfile = $user->retailerProfile;
        $taxIdentifier = $request->input('tax_identifier');

        $taxIdentifierRules = [
            'nullable',
            'string',
            'max:32',
        ];

        if ($existingProfile === null || $taxIdentifier !== $existingProfile->tax_identifier) {
            $taxIdentifierRules[] = Rule::unique('retailer_profiles', 'tax_identifier')
                ->ignore($existingProfile?->id);
        }

        $validated = $request->validate([
            'legal_business_name' => ['required', 'string', 'max:190'],
            'tax_identifier' => $taxIdentifierRules,
            'business_phone' => ['nullable', 'string', 'max:32'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $user = $request->user()->fresh();
            $existingProfile = $user->retailerProfile;
            abort_if($existingProfile?->verification_status === 'approved', 409, 'Firma została już zatwierdzona.');

            RetailerProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    ...$validated,
                    'verification_status' => 'pending',
                    'submitted_at' => now(),
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                    'review_notes' => null,
                ]
            );

            SecurityEvent::query()->create([
                'user_id' => $user->id,
                'event_type' => 'retailer_application_submitted',
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('account.show')->with('status', 'retailer-application-submitted');
    }
}
