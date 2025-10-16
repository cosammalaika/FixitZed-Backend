<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fixer_id',
        'service_id',
        'scheduled_at',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'fixer_id' => 'integer',
        'service_id' => 'integer',
        'scheduled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fixer()
    {
        return $this->belongsTo(User::class, 'fixer_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
