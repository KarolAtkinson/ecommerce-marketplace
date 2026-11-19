<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'retailer_profile_id',
    'reference',
    'title',
    'contract_type',
    'internal_owner_id',
    'effective_date',
    'expiry_date',
    'status',
    'notice_period_days',
])]
class RetailerContract extends Model
{
    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function retailerProfile(): BelongsTo
    {
        return $this->belongsTo(RetailerProfile::class);
    }

    public function internalOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'internal_owner_id');
    }
}
