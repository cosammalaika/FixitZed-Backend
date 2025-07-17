<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'is_active',
    ];

    // Optional: Add relationships here

    // Example: If services belong to a category
    // public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }

    // Example: If services are offered by many providers
    // public function providers()
    // {
    //     return $this->belongsToMany(User::class, 'provider_service');
    // }
}