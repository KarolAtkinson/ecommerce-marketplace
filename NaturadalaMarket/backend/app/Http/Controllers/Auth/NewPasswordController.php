<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'requestEmail' => $request->query('email', ''),
            'token' => $request->route('token'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'confirmed', PasswordRules::defaults()],
        ]);

        $user = User::query()->where('email', $request->string('email')->lower())->first();
        if ($user && $user->status !== 'active') {
            return back()->withErrors(['email' => 'Link resetujący jest nieprawidłowy lub wygasł.']);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request): void {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                SecurityEvent::query()->create([
                    'user_id' => $user->id,
                    'event_type' => 'password_reset',
                    'ip_address' => $request->ip(),
                ]);

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Hasło zostało zmienione. Zaloguj się ponownie.')
            : back()->withErrors(['email' => 'Link resetujący jest nieprawidłowy lub wygasł.']);
    }
}
