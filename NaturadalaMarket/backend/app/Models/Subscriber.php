<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

#[Fillable(['email', 'locale', 'consented_at', 'confirmed_at', 'unsubscribed_at'])]
class Subscriber extends Model
{
    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('confirmed_at')->whereNull('unsubscribed_at');
    }

    public function confirmUrl(): string
    {
        return URL::temporarySignedRoute('subscription.confirm', now()->addDays(7), ['subscriber' => $this->getKey()]);
    }

    public function unsubscribeUrl(): string
    {
        return URL::signedRoute('subscription.unsubscribe', ['subscriber' => $this->getKey()]);
    }
}
