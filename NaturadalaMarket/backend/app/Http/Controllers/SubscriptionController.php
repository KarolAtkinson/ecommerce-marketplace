<?php

namespace App\Http\Controllers;

use App\Mail\SubscriptionConfirmation;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ], [
            'email.required' => 'Podaj adres e-mail.',
            'email.email' => 'Podaj poprawny adres e-mail.',
            'consent.accepted' => 'Zaznacz zgodę, aby zapisać się do newslettera.',
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $subscriber = Subscriber::query()->firstOrNew(['email' => $email]);

        if (! $subscriber->exists || ! $subscriber->confirmed_at || $subscriber->unsubscribed_at) {
            $subscriber->fill([
                'locale' => $request->session()->get('locale', 'pl'),
                'consented_at' => now(),
                'confirmed_at' => null,
                'unsubscribed_at' => null,
            ])->save();

            Mail::to($subscriber->email)->send(new SubscriptionConfirmation($subscriber));
        }

        // The same answer for new and existing addresses avoids revealing who is subscribed.
        return redirect()->to(url()->previous().'#newsletter')->with('subscription_status', 'check-email');
    }

    public function confirm(Subscriber $subscriber): View
    {
        $subscriber->forceFill(['confirmed_at' => $subscriber->confirmed_at ?? now(), 'unsubscribed_at' => null])->save();

        return view('subscription.result', ['state' => 'confirmed']);
    }

    public function unsubscribePage(Subscriber $subscriber): View
    {
        return view('subscription.unsubscribe', ['subscriber' => $subscriber]);
    }

    public function unsubscribe(Subscriber $subscriber): View
    {
        $subscriber->forceFill(['unsubscribed_at' => now()])->save();

        return view('subscription.result', ['state' => 'unsubscribed']);
    }
}
