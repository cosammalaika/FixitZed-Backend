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
        'service_request_id' => 'integer',
        'amount' => 'float',
        'original_amount' => 'float',
        'discount_amount' => 'float',
        'coupon_id' => 'integer',
        'loyalty_points_used' => 'integer',
        'paid_at' => 'datetime',
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
