<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Potwierdź zapis do newslettera Naturadała');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.subscription.confirm', with: [
            'confirmUrl' => $this->subscriber->confirmUrl(),
            'discount' => config('features.subscriber_discount_percent'),
        ]);
    }
}
