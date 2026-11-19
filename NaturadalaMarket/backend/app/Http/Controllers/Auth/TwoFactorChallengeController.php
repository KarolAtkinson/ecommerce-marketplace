<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): RedirectResponse|View
    {
        if (! $this->challengeIsValid($request)) {
            $request->session()->forget('mfa.challenge');

            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request, Google2FA $google2fa): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:32'],
        ]);

        if (! $this->challengeIsValid($request)) {
            $request->session()->forget('mfa.challenge');

            return redirect()->route('login');
        }

        $userId = (int) $request->session()->get('mfa.challenge.user_id');
        $credentialFingerprint = $request->session()->get('mfa.challenge.credential_fingerprint');
        $code = strtoupper(trim(str_replace([' ', '-'], '', $validated['code'])));

        $user = DB::transaction(function () use ($userId, $credentialFingerprint, $code, $request, $google2fa): ?User {
            $user = User::query()->lockForUpdate()->find($userId);

            if (
                ! $user ||
                $user->status !== 'active' ||
                ! $user->two_factor_confirmed_at ||
                ! $user->two_factor_secret ||
                ! is_string($credentialFingerprint) ||
                ! hash_equals($credentialFingerprint, $this->credentialFingerprint($user))
            ) {
                return null;
            }

            $step = preg_match('/^\d{6}$/', $code)
                ? $google2fa->verifyKeyNewer(
                    $user->two_factor_secret,
                    $code,
                    $user->two_factor_last_used_step ?? -1,
                    1
                )
                : false;

            if ($step !== false) {
                $user->forceFill(['two_factor_last_used_step' => $step])->save();
            } else {
                $recoveryCodes = $user->two_factor_recovery_codes ?? [];
                $matchedIndex = null;

                foreach ($recoveryCodes as $index => $hashedCode) {
                    if (Hash::check($code, $hashedCode)) {
                        $matchedIndex = $index;

                        break;
                    }
                }

                if ($matchedIndex === null) {
                    return null;
                }

                unset($recoveryCodes[$matchedIndex]);
                $user->forceFill([
                    'two_factor_recovery_codes' => array_values($recoveryCodes),
                ])->save();
            }

            SecurityEvent::query()->create([
                'user_id' => $user->id,
                'event_type' => 'mfa_challenge_succeeded',
                'ip_address' => $request->ip(),
            ]);

            return $user;
        });

        if (! $user) {
            if (User::query()->whereKey($userId)->exists()) {
                SecurityEvent::query()->create([
                    'user_id' => $userId,
                    'event_type' => 'mfa_challenge_failed',
                    'ip_address' => $request->ip(),
                ]);
            }

            throw ValidationException::withMessages([
                'code' => 'Kod weryfikacyjny jest nieprawidłowy lub został już użyty.',
            ]);
        }

        $request->session()->forget('mfa.challenge');
        Auth::login($user);
        $request->session()->regenerate();

        $defaultDestination = $user->isApprovedRetailer()
            ? route('retailer.workspace')
            : route('account.show');

        return redirect()->intended($defaultDestination);
    }

    private function challengeIsValid(Request $request): bool
    {
        $userId = $request->session()->get('mfa.challenge.user_id');
        $credentialFingerprint = $request->session()->get('mfa.challenge.credential_fingerprint');

        if (
            ! is_numeric($userId) ||
            ! is_string($credentialFingerprint) ||
            $request->session()->get('mfa.challenge.expires_at', 0) < now()->timestamp
        ) {
            return false;
        }

        $user = User::query()->find((int) $userId);

        return $user !== null &&
            $user->status === 'active' &&
            $user->two_factor_confirmed_at !== null &&
            $user->two_factor_secret !== null &&
            hash_equals($credentialFingerprint, $this->credentialFingerprint($user));
    }

    private function credentialFingerprint(User $user): string
    {
        return hash('sha256', $user->password."\0".$user->email);
    }
}
