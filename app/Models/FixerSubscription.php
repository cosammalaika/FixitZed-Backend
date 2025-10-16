<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixerSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixer_id',
        'subscription_plan_id',
        'payment_reference',
        'payment_method',
        'payment_instructions',
        'payment_meta',
        'status',
        'coins_awarded',
        'starts_at',
        'expires_at',
        'approved_at',
        'amount_paid_cents',
        'loyalty_points_used',
        'loyalty_points_awarded',
        'loyalty_deducted_at',
        'loyalty_awarded_at',
    ];

    protected $casts = [
        'fixer_id' => 'integer',
        'subscription_plan_id' => 'integer',
        'coins_awarded' => 'integer',
        'amount_paid_cents' => 'integer',
        'loyalty_points_used' => 'integer',
        'loyalty_points_awarded' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'loyalty_deducted_at' => 'datetime',
        'loyalty_awarded_at' => 'datetime',
        'payment_meta' => 'array',
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
