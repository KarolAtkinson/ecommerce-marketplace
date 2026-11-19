<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'phone',
    'company_name',
    'message',
    'products',
    'status',
])]
class Inquiry extends Model
{
    protected function casts(): array
    {
        return [
            'products' => 'array',
        ];
    }
}
