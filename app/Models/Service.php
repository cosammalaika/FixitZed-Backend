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

    protected static ?bool $hasIsActive = null;

    public function scopeActive($query)
    {
        // Determine schema once per process to avoid per-query checks.
        if (! isset(static::$hasIsActive)) {
            static::$hasIsActive = Schema::hasColumn('services', 'is_active');
        }

        if (static::$hasIsActive) {
            return $query->where('is_active', true);
        }

        return $query->whereRaw('LOWER(status) = ?', ['active']);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
