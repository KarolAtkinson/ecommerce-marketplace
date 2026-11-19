<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationController extends Controller
{
    public function create(Request $request, Google2FA $google2fa): View
    {
        $user = $request->user();
        $setup = $request->session()->get('mfa.setup');

        if (! $user->two_factor_confirmed_at && (
            ! is_array($setup) ||
            ($setup['expires_at'] ?? 0) < now()->timestamp
        )) {
            $setup = [
                'secret' => Crypt::encryptString($google2fa->generateSecretKey()),
                'expires_at' => now()->addMinutes(10)->timestamp,
            ];

            $request->session()->put('mfa.setup', $setup);
        }

        return view('auth.two-factor-settings', [
            'enabled' => $user->two_factor_confirmed_at !== null,
            'secret' => $user->two_factor_confirmed_at
                ? null
                : Crypt::decryptString($setup['secret']),
        ]);
    }

    public function store(Request $request, Google2FA $google2fa): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'current_password'],
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        $setup = $request->session()->get('mfa.setup');

        if (
            $user->two_factor_confirmed_at ||
            ! is_array($setup) ||
            ($setup['expires_at'] ?? 0) < now()->timestamp
        ) {
            $request->session()->forget('mfa.setup');

            throw ValidationException::withMessages([
                'code' => 'Weryfikacja wygasła. Rozpocznij ponownie konfigurację uwierzytelniania.',
            ]);
        }

        $secret = Crypt::decryptString($setup['secret']);
        $step = $google2fa->verifyKeyNewer($secret, $validated['code'], -1, 1);

        if ($step === false) {
            throw ValidationException::withMessages([
                'code' => 'Kod weryfikacyjny jest nieprawidłowy.',
            ]);
        }

        $recoveryCodes = collect(range(1, 8))
            ->map(fn (): string => Str::upper(Str::random(8).'-'.Str::random(8)))
            ->all();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => array_map(
                fn (string $code): string => Hash::make(str_replace('-', '', $code)),
                $recoveryCodes
            ),
            'two_factor_confirmed_at' => now(),
            'two_factor_last_used_step' => $step,
        ])->save();

        SecurityEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'mfa_enabled',
            'ip_address' => $request->ip(),
        ]);

        $request->session()->forget('mfa.setup');

        return redirect()->route('mfa.show')->with('recoveryCodes', $recoveryCodes);
    }

    public function destroy(Request $request, Google2FA $google2fa): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'current_password'],
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        $step = $user->two_factor_secret && $user->two_factor_last_used_step !== null
            ? $google2fa->verifyKeyNewer(
                $user->two_factor_secret,
                $validated['code'],
                $user->two_factor_last_used_step,
                1
            )
            : false;

        if (! $user->two_factor_confirmed_at || $step === false) {
            throw ValidationException::withMessages([
                'code' => 'Nie udało się zweryfikować kodu. Uwierzytelnianie pozostaje włączone.',
            ]);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_used_step' => null,
        ])->save();

        SecurityEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'mfa_disabled',
            'ip_address' => $request->ip(),
        ]);

        $request->session()->regenerate();

        return redirect()->route('mfa.show')->with('status', 'mfa-disabled');
    }

    public function regenerateRecoveryCodes(Request $request, Google2FA $google2fa): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'code' => ['required', 'digits:6'],
        ]);

        $recoveryCodes = DB::transaction(function () use ($request, $validated, $google2fa): array {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);

            $step = $user->two_factor_secret && $user->two_factor_last_used_step !== null
                ? $google2fa->verifyKeyNewer(
                    $user->two_factor_secret,
                    $validated['code'],
                    $user->two_factor_last_used_step,
                    1
                )
                : false;

            if (! $user->two_factor_confirmed_at || $step === false) {
                throw ValidationException::withMessages([
                    'code' => 'Nie udało się zweryfikować kodu. Kody odzyskiwania nie zostały zmienione.',
                ]);
            }

            $recoveryCodes = collect(range(1, 8))
                ->map(fn (): string => Str::upper(Str::random(8).'-'.Str::random(8)))
                ->all();

            $user->forceFill([
                'two_factor_recovery_codes' => array_map(
                    fn (string $code): string => Hash::make(str_replace('-', '', $code)),
                    $recoveryCodes
                ),
                'two_factor_last_used_step' => $step,
            ])->save();

            SecurityEvent::query()->create([
                'user_id' => $user->id,
                'event_type' => 'mfa_recovery_codes_regenerated',
                'ip_address' => $request->ip(),
            ]);

            return $recoveryCodes;
        });

        return redirect()->route('mfa.show')->with('recoveryCodes', $recoveryCodes);
    }
}
