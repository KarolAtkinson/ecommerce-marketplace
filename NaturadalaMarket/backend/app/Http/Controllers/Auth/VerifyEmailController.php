<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->fulfill();

            SecurityEvent::query()->create([
                'user_id' => $request->user()->id,
                'event_type' => 'email_verified',
                'ip_address' => $request->ip(),
            ]);

        }

        return redirect()->route('account.show')->with('status', 'email-verified');
    }
}
