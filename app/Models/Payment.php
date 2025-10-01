<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'amount',
        'original_amount',
        'discount_amount',
        'status',
        'payment_method',
        'transaction_id',
        'paid_at',
        'coupon_id',
        'loyalty_points_used',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'loyalty_points_used' => 'integer',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
