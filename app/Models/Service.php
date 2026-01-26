<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function fixers()
    {
        return $this->belongsToMany(Fixer::class, 'fixer_service');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function scopeActive($query)
    {
        // Production schema: use is_active as the single activation flag.
        return $query->where('is_active', true);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
