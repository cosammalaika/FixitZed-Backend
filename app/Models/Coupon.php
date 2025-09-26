<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'discount_percent',
        'discount_amount',
        'valid_from',
        'valid_to',
        'usage_limit',
        'used_count',
    ];

    protected $dates = [
        'valid_from',
        'valid_to',
    ];

    /**
     * Check if the coupon is currently valid.
     */
    public function isValid(): bool
    {
        $now = now();
        return $this->valid_from <= $now && $this->valid_to >= $now && $this->used_count < $this->usage_limit;
    }
}
