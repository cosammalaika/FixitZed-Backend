<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcategory_id',
        'name',
        'description',
        'price',
        'duration_minutes',
        // You can add 'is_active' if you have that column in your DB; your migration doesn't show it
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
