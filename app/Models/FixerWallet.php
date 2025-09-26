<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixerWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixer_id', 'coin_balance', 'subscription_status', 'last_subscription_expires_at',
    ];

    protected $casts = [
        'last_subscription_expires_at' => 'datetime',
    ];

    public function fixer()
    {
        return $this->belongsTo(Fixer::class);
    }
}

