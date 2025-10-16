<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'rater_id',
        'rated_user_id',
        'service_request_id',
        'role',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rater_id' => 'integer',
        'rated_user_id' => 'integer',
        'service_request_id' => 'integer',
        'rating' => 'float',
    ];

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function ratedUser()
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
