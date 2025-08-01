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
        'is_active',
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
    public function fixers()
    {
        return $this->belongsToMany(Fixer::class, 'fixer_service');
    }
    

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
