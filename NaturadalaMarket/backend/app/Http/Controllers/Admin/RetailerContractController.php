<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RetailerContract;
use App\Models\RetailerProfile;
use App\Models\Role;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RetailerContractController extends Controller
{
    private const STATUSES = ['draft', 'review', 'signed', 'active', 'expired', 'terminated'];

    public function index(): View
    {
        return view('admin.retailers.contracts', [
            'retailers' => RetailerProfile::query()
                ->with('user')
                ->where('verification_status', 'approved')
                ->orderBy('legal_business_name')
                ->get(),
            'contracts' => RetailerContract::query()
                ->with(['retailerProfile', 'internalOwner'])
                ->latest()
                ->paginate(20),
            'owners' => User::query()
                ->where('status', 'active')
                ->whereHas('roles', fn ($query) => $query->where('slug', 'admin'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $profile = RetailerProfile::query()
            ->whereKey($validated['retailer_profile_id'])
            ->where('verification_status', 'approved')
            ->firstOrFail();

        DB::transaction(function () use ($request, $validated, $profile): void {
            $contract = $profile->contracts()->create([
                ...$validated,
                'internal_owner_id' => $validated['internal_owner_id'] ?? null,
            ]);
            $this->recordEvent($request, $profile, 'retailer_contract_created', $contract);
        });

        return back()->with('status', 'retailer-contract-created');
    }

    public function update(Request $request, RetailerContract $contract): RedirectResponse
    {
        $validated = $request->validate($this->rules($contract));

        DB::transaction(function () use ($request, $contract, $validated): void {
            $lockedContract = RetailerContract::query()->whereKey($contract->id)->lockForUpdate()->firstOrFail();
            $previousProfile = $lockedContract->retailerProfile;
            abort_unless(
                RetailerProfile::query()
                    ->whereKey($validated['retailer_profile_id'])
                    ->where('verification_status', 'approved')
                    ->exists(),
                422,
                'Umowę można przypisać wyłącznie do zatwierdzonej firmy.'
            );
            $lockedContract->update([
                ...$validated,
                'internal_owner_id' => $validated['internal_owner_id'] ?? null,
            ]);
            $lockedContract->load('retailerProfile');
            $this->recordEvent(
                $request,
                $lockedContract->retailerProfile,
                'retailer_contract_updated',
                $lockedContract,
                ['previous_retailer_profile_id' => $previousProfile->id]
            );
            if ($previousProfile->id !== $lockedContract->retailer_profile_id) {
                $this->recordEvent(
                    $request,
                    $previousProfile,
                    'retailer_contract_reassigned',
                    $lockedContract,
                    ['new_retailer_profile_id' => $lockedContract->retailer_profile_id]
                );
            }
        });

        return back()->with('status', 'retailer-contract-updated');
    }

    public function destroy(Request $request, RetailerContract $contract): RedirectResponse
    {
        DB::transaction(function () use ($request, $contract): void {
            $lockedContract = RetailerContract::query()->whereKey($contract->id)->lockForUpdate()->firstOrFail();
            $profile = $lockedContract->retailerProfile;
            $this->recordEvent($request, $profile, 'retailer_contract_deleted', $lockedContract);
            $lockedContract->delete();
        });

        return back()->with('status', 'retailer-contract-deleted');
    }

    private function rules(?RetailerContract $contract = null): array
    {
        $adminRoleId = Role::query()->where('slug', 'admin')->value('id');

        return [
            'retailer_profile_id' => ['required', 'integer', 'exists:retailer_profiles,id'],
            'reference' => [
                'required',
                'string',
                'max:64',
                Rule::unique('retailer_contracts', 'reference')
                    ->where('retailer_profile_id', request()->input('retailer_profile_id'))
                    ->ignore($contract?->id),
            ],
            'title' => ['required', 'string', 'max:190'],
            'contract_type' => ['required', 'string', 'max:100'],
            'internal_owner_id' => [
                'nullable',
                'integer',
                Rule::exists('user_roles', 'user_id')->where(fn ($query) => $query->where('role_id', $adminRoleId)),
            ],
            'effective_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
            'status' => ['required', Rule::in(self::STATUSES)],
            'notice_period_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
        ];
    }

    private function recordEvent(
        Request $request,
        RetailerProfile $profile,
        string $eventType,
        RetailerContract $contract,
        array $additionalMetadata = []
    ): void {
        SecurityEvent::query()->create([
            'user_id' => $profile->user_id,
            'event_type' => $eventType,
            'metadata' => [
                'contract_id' => $contract->id,
                'admin_user_id' => $request->user()->id,
                ...$additionalMetadata,
            ],
        ]);
    }
}
