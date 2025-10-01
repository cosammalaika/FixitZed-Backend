<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'active',
        'sort_order',
        'requires_integration',
        'integration_note',
    ];
    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
        'requires_integration' => 'boolean',
    ];
}
