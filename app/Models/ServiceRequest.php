<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'fixer_id',
        'service_id',
        'scheduled_at',
        'status',
        'location',
        'location_lat',
        'location_lng',
        'fixer_snoozed_until',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'fixer_snoozed_until' => 'datetime',
        'location_lat' => 'float',
        'location_lng' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function fixer()
    {
        return $this->belongsTo(Fixer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function declines()
    {
        return $this->hasMany(ServiceRequestDecline::class);
    }
}
