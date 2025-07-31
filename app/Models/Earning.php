<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixer_id',
        'service_count',
        'amount',
    ];

    public function fixer()
    {
        return $this->belongsTo(Fixer::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_count');
    }
}
