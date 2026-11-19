<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = mb_strtolower($credentials['email']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            SecurityEvent::query()->create([
                'user_id' => User::query()->where('email', mb_strtolower($credentials['email']))->value('id'),
                'event_type' => 'login_failed',
                'ip_address' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Dane logowania są nieprawidłowe lub konto jest niedostępne.',
            ]);
        }

        $user = $request->user();
        if ($user->status !== 'active') {
            SecurityEvent::query()->create([
                'user_id' => $user->id,
                'event_type' => 'login_blocked',
                'ip_address' => $request->ip(),
            ]);

            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Dane logowania są nieprawidłowe lub konto jest niedostępne.',
            ]);
        }

        $request->session()->regenerate();
        $defaultDestination = $this->defaultDestination($user);

        if ($user->two_factor_confirmed_at) {
            $intendedUrl = $request->session()->pull('url.intended', $defaultDestination);
            Auth::logout();
            $request->session()->regenerate();
            $request->session()->put('url.intended', $intendedUrl);
            $request->session()->put('mfa.challenge', [
                'user_id' => $user->id,
                'credential_fingerprint' => hash('sha256', $user->password."\0".$user->email),
                'expires_at' => now()->addMinutes(5)->timestamp,
            ]);

            return redirect()->route('two-factor.login');
        }

        SecurityEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'login_succeeded',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->intended($defaultDestination);
    }

    private function defaultDestination(User $user): string
    {
        return $user->isApprovedRetailer()
            ? route('retailer.workspace')
            : route('account.show');
    }

    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user()) {
            SecurityEvent::query()->create([
                'user_id' => $request->user()->id,
                'event_type' => 'logout',
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
