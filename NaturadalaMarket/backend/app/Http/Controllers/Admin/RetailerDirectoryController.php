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

class RetailerDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'account' => ['nullable', Rule::in(['active', 'suspended'])],
        ]);
        $search = trim($filters['q'] ?? '');

        $retailers = RetailerProfile::query()
            ->with(['user.roles'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('verification_status', $status))
            ->when(($filters['account'] ?? null) === 'active', fn ($query) => $query->whereHas('user', fn ($user) => $user->where('status', 'active')))
            ->when(($filters['account'] ?? null) === 'suspended', fn ($query) => $query->whereHas('user', fn ($user) => $user->where('status', 'suspended')))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('legal_business_name', 'like', "%{$search}%")
                    ->orWhere('tax_identifier', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            }))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.retailers.directory', [
            'retailers' => $retailers,
            'filters' => $filters,
        ]);
    }

    public function updateAccess(Request $request, RetailerProfile $profile): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['suspend', 'reactivate', 'revoke'])],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['action'] !== 'reactivate' && trim((string) ($validated['reason'] ?? '')) === '') {
            return back()->withErrors([
                'reason' => 'Podaj powód tej zmiany, aby zachować czytelną historię administracyjną.',
            ]);
        }

        DB::transaction(function () use ($request, $profile, $validated): void {
            $profile = RetailerProfile::query()
                ->whereKey($profile->id)
                ->lockForUpdate()
                ->with('user')
                ->firstOrFail();
            $user = $profile->user;

            abort_if(
                $validated['action'] !== 'revoke' && $profile->verification_status !== 'approved',
                409,
                'Tylko zatwierdzona firma może zostać zawieszona lub przywrócona.'
            );

            if ($validated['action'] === 'suspend') {
                abort_if($user->status === 'suspended', 409, 'Konto firmy jest już zawieszone.');
                $user->forceFill(['status' => 'suspended', 'suspended_at' => now()])->save();
                $eventType = 'retailer_suspended';
            } elseif ($validated['action'] === 'reactivate') {
                abort_if($user->status !== 'suspended', 409, 'Konto firmy nie jest zawieszone.');
                abort_if(! $user->hasVerifiedEmail(), 409, 'Przed przywróceniem firma musi mieć potwierdzony adres e-mail.');
                $user->forceFill(['status' => 'active', 'suspended_at' => null])->save();
                $eventType = 'retailer_reactivated';
            } else {
                abort_if($profile->verification_status !== 'approved', 409, 'Dostęp hurtowy nie jest aktywny.');
                $profile->update([
                    'verification_status' => 'rejected',
                    'reviewed_at' => now(),
                    'reviewed_by' => $request->user()->id,
                    'review_notes' => $validated['reason'],
                ]);
                $user->roles()->detach(Role::query()->where('slug', 'retailer')->firstOrFail());
                $eventType = 'retailer_access_revoked';
            }

            SecurityEvent::query()->create([
                'user_id' => $user->id,
                'event_type' => $eventType,
                'metadata' => [
                    'admin_user_id' => $request->user()->id,
                    'reason' => $validated['reason'] ?? null,
                ],
            ]);
        });

        return back()->with('status', 'retailer-access-updated');
    }
}
