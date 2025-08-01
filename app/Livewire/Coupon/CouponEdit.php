<?php

namespace App\Livewire\Coupon;

use App\Models\Coupon;
use Livewire\Component;

class CouponEdit extends Component
{
    public $id;

    public $code, $discount_percent, $valid_from, $valid_to, $usage_limit;

    public function mount(Coupon $id)
    {
        $this->coupon = $id;
        $this->code = $id->code;
        $this->discount_percent = $id->discount_percent;
        $this->valid_from = $id->valid_from;
        $this->valid_to = $id->valid_to;
        $this->usage_limit = $id->usage_limit;
    }

    public function update()
    {
        $this->validate([
            'code' => 'required|string|unique:coupons,code,' . $this->coupon->id,
            'discount_percent' => 'required|integer|min:1|max:100',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after_or_equal:valid_from',
            'usage_limit' => 'required|integer|min:1',
        ]);

        $this->coupon->update([
            'code' => strtoupper($this->code),
            'discount_percent' => $this->discount_percent,
            'valid_from' => $this->valid_from,
            'valid_to' => $this->valid_to,
            'usage_limit' => $this->usage_limit,
        ]);

        log_user_action('updated coupon', "Updated coupon ID: {$this->coupon->id}, Code: {$this->code}");

        session()->flash('success', 'Coupon updated successfully!');
        return redirect()->route('coupon.index');
    }
    public function render()
    {
        return view('livewire.coupon.coupon-edit');
    }
}
