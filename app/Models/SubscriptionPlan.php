<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'price_cents', 'coins', 'valid_days', 'is_active',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'coins' => 'integer',
        'valid_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(FixerSubscription::class);
    }
}
