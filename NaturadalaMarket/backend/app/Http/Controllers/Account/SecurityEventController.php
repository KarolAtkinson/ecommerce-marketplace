<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SecurityEventController extends Controller
{
    public function index(Request $request): View
    {
        $events = $request->user()
            ->securityEvents()
            ->latest('created_at')
            ->latest('id')
            ->paginate(20);

        return view('account.security-events', ['events' => $events]);
    }

    public function revokeOtherSessions(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        Auth::logoutOtherDevices($validated['current_password']);

        SecurityEvent::query()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'other_sessions_revoked',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('account.security-events.index')
            ->with('status', 'other-sessions-revoked');
    }
}
