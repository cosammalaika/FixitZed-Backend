<?php

namespace App\Models;

use App\Services\PriorityPointService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fixer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'status',
        'rating_avg',
        'priority_points',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'priority_points' => 'integer',
        'rating_avg' => 'float',
        'last_offered_at' => 'datetime',
        'last_assigned_at' => 'datetime',
        'last_completed_at' => 'datetime',
        'last_idle_bonus_at' => 'datetime',
        'priority_low_since_at' => 'datetime',
    ];

    protected $attributes = [
        'priority_points' => 100,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'fixer_service');
    }


    public function earnings()
    {
        return $this->hasMany(Earning::class);
    }

    public function wallet()
    {
        return $this->hasOne(FixerWallet::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(FixerSubscription::class);
    }

    public function declines()
    {
        return $this->hasMany(ServiceRequestDecline::class);
    }

    public function priorityHistory()
    {
        return $this->hasMany(PriorityPointLog::class)->latest();
    }

    public function adjustPriorityPoints(
        int $delta,
        string $reason,
        array $meta = [],
        ?int $performedBy = null,
        ?int $floor = null,
        ?int $cap = null
    ) {
        return app(PriorityPointService::class)->adjust(
            $this,
            $delta,
            $reason,
            $meta,
            $performedBy,
            $floor,
            $cap
        );
    }
}
