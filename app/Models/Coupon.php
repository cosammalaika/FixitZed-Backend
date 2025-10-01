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

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_to && $this->valid_to->isPast()) {
            return false;
        }

        $limit = $this->usage_limit;
        $used = $this->used_count ?? 0;
        if ($limit !== null && $limit > 0 && $used >= $limit) {
            return false;
        }

        return true;
    }
}
