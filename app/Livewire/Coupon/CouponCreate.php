<?php

namespace App\Livewire\Coupon;

use App\Models\Coupon;
use Livewire\Component;

class CouponCreate extends Component
{
    public $code, $title, $description, $discount_percent, $discount_amount, $valid_from, $valid_to, $usage_limit;

    public function save()
    {
        $this->validate([
            'code' => 'required|string|unique:coupons,code',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_percent' => 'required|integer|min:1|max:100',
            'discount_amount' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after_or_equal:valid_from',
            'usage_limit' => 'required|integer|min:1',
        ]);

        $coupon = Coupon::create([
            'code' => strtoupper($this->code),
            'title' => $this->title,
            'description' => $this->description,
            'discount_percent' => $this->discount_percent,
            'discount_amount' => $this->discount_amount,
            'valid_from' => $this->valid_from,
            'valid_to' => $this->valid_to,
            'usage_limit' => $this->usage_limit,
        ]);

        log_user_action('created coupon', "Created coupon ID: {$coupon->id}, Code: {$coupon->code}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Coupon created successfully!',
            'redirect' => route('coupon.index'),
        ]);
    }
    public function render()
    {
        return view('livewire.coupon.coupon-create');
    }
}
