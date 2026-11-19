<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RetailerProfile;
use App\Models\Role;
use App\Models\SecurityEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RetailerReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $search = trim($filters['q'] ?? '');
        $applications = RetailerProfile::query()
            ->with('user')
            ->where('verification_status', 'pending')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('legal_business_name', 'like', "%{$search}%")
                    ->orWhere('tax_identifier', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            }))
            ->orderBy('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.retailers.index', [
            'applications' => $applications,
            'filters' => $filters,
        ]);
    }

    public function update(Request $request, RetailerProfile $profile): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['decision'] === 'reject' && trim((string) ($validated['review_notes'] ?? '')) === '') {
            return back()->withErrors([
                'review_notes' => 'Wpisz powód odmowy, aby użytkownik mógł zrozumieć decyzję.',
            ]);
        }

        $filters = $request->validate([
            'filter_q' => ['nullable', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($request, $profile, $validated): void {
            $profile = RetailerProfile::query()
                ->whereKey($profile->id)
                ->lockForUpdate()
                ->with('user')
                ->firstOrFail();

            abort_if($profile->verification_status !== 'pending', 409, 'Ta aplikacja została już rozpatrzona.');
            abort_if(
                $profile->user->status !== 'active' || ! $profile->user->hasVerifiedEmail(),
                409,
                'Konto musi być aktywne i mieć potwierdzony adres e-mail.'
            );

            $approved = $validated['decision'] === 'approve';
            $profile->update([
                'verification_status' => $approved ? 'approved' : 'rejected',
                'reviewed_at' => now(),
                'reviewed_by' => $request->user()->id,
                'review_notes' => $validated['review_notes'] ?? null,
            ]);

            $retailerRole = Role::query()->where('slug', 'retailer')->firstOrFail();
            if ($approved) {
                $profile->user->roles()->syncWithoutDetaching([
                    $retailerRole->id => ['assigned_by' => $request->user()->id],
                ]);
            } else {
                $profile->user->roles()->detach($retailerRole);
            }

            SecurityEvent::query()->create([
                'user_id' => $profile->user_id,
                'event_type' => $approved ? 'retailer_application_approved' : 'retailer_application_rejected',
                'metadata' => [
                    'reviewed_by' => $request->user()->id,
                    'review_notes' => $validated['review_notes'] ?? null,
                ],
            ]);
        });

        return redirect()->route('admin.retailers.index', array_filter([
            'q' => $filters['filter_q'] ?? null,
        ], fn (?string $value): bool => $value !== null && $value !== ''))
            ->with('status', 'retailer-application-reviewed');
    }
}
