<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixer_id',
        'source_service_request_id',
        'amount',
    ];

    public function fixer()
    {
        return $this->belongsTo(Fixer::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'source_service_request_id');
    }
}
