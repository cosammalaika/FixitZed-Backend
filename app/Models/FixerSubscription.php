<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixerSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixer_id', 'subscription_plan_id', 'payment_reference', 'status',
        'coins_awarded', 'starts_at', 'expires_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function fixer()
    {
        return $this->belongsTo(Fixer::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}

