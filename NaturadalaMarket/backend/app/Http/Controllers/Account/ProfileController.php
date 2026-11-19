<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $request->user()->update([
            'name' => trim($validated['name']),
            'phone' => filled($validated['phone'] ?? null) ? trim($validated['phone']) : null,
        ]);

        return redirect()->route('account.show')->with('status', 'profile-updated');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'current_password' => ['required', 'current_password'],
        ]);

        if ($validated['email'] === mb_strtolower($request->user()->email)) {
            return redirect()->route('account.profile.edit')->with('status', 'email-unchanged');
        }

        $request->user()->forceFill([
            'email' => $validated['email'],
            'email_verified_at' => null,
            'remember_token' => Str::random(60),
        ])->save();

        Auth::logoutOtherDevices($request->input('current_password'));
        $request->session()->regenerate();
        $request->user()->sendEmailVerificationNotification();

        SecurityEvent::query()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'email_change_requested',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('verification.notice')->with('status', 'email-change-verification-sent');
    }
}
