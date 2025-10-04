<?php

namespace App\Models;

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
}
