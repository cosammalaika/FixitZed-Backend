<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestDecline extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'fixer_id',
        'declined_at',
    ];

    protected $casts = [
        'declined_at' => 'datetime',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function fixer()
    {
        return $this->belongsTo(Fixer::class);
    }
}
