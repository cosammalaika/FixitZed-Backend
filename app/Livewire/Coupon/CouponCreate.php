<?php

namespace App\Livewire\Coupon;

use App\Models\Coupon;
use Livewire\Component;

class CouponCreate extends Component
{
    public $code, $discount_percent, $valid_from, $valid_to, $usage_limit;

    public function save()
    {
        $this->validate([
            'code' => 'required|string|unique:coupons,code',
            'discount_percent' => 'required|integer|min:1|max:100',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after_or_equal:valid_from',
            'usage_limit' => 'required|integer|min:1',
        ]);

        $coupon = Coupon::create([
            'code' => strtoupper($this->code),
            'discount_percent' => $this->discount_percent,
            'valid_from' => $this->valid_from,
            'valid_to' => $this->valid_to,
            'usage_limit' => $this->usage_limit,
        ]);

        log_user_action('created coupon', "Created coupon ID: {$coupon->id}, Code: {$coupon->code}");

        session()->flash('success', 'Coupon created successfully!');
        return redirect()->route('coupons.index');
    }
    public function render()
    {
        return view('livewire.coupon.coupon-create');
    }
}
