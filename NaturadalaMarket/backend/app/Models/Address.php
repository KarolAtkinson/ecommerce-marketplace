<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $country_code
 * @property bool $is_default_billing
 * @property bool $is_default_shipping
 */
#[Fillable([
    'label',
    'recipient_name',
    'company_name',
    'street_line_1',
    'street_line_2',
    'postal_code',
    'city',
    'region',
    'country_code',
    'phone',
    'is_default_billing',
    'is_default_shipping',
])]
class Address extends Model
{
    protected function casts(): array
    {
        return [
            'is_default_billing' => 'boolean',
            'is_default_shipping' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
