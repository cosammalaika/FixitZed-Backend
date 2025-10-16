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

    protected $casts = [
        'fixer_id' => 'integer',
        'service_count' => 'integer',
        'amount' => 'float',
    ];

    public function fixer()
    {
        return $this->belongsTo(Fixer::class);
    }

}
