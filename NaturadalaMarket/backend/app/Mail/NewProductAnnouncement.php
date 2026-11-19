<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewProductAnnouncement extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Product $product, public Subscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nowość w Naturadała: '.$this->product->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.subscription.new-product', with: [
            'productUrl' => route('products.show', $this->product),
            'unsubscribeUrl' => $this->subscriber->unsubscribeUrl(),
            'discount' => config('features.subscriber_discount_percent'),
        ]);
    }
}
