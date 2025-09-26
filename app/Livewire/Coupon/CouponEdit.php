<?php

namespace App\Livewire\Coupon;

use App\Models\Coupon;
use Livewire\Component;

class CouponEdit extends Component
{
    public $id;
    public $coupon; // holds the bound Coupon model

    public $code, $title, $description, $discount_percent, $discount_amount, $valid_from, $valid_to, $usage_limit;

    public function mount(Coupon $id)
    {
        $this->coupon = $id;
        $this->code = $id->code;
        $this->title = $id->title;
        $this->description = $id->description;
        $this->discount_percent = $id->discount_percent;
        $this->discount_amount = $id->discount_amount;
        $this->valid_from = $id->valid_from;
        $this->valid_to = $id->valid_to;
        $this->usage_limit = $id->usage_limit;
    }

    public function update()
    {
        $this->validate([
            'code' => 'required|string|unique:coupons,code,' . $this->coupon->id,
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_percent' => 'required|integer|min:1|max:100',
            'discount_amount' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after_or_equal:valid_from',
            'usage_limit' => 'required|integer|min:1',
        ]);

        $this->coupon->update([
            'code' => strtoupper($this->code),
            'title' => $this->title,
            'description' => $this->description,
            'discount_percent' => $this->discount_percent,
            'discount_amount' => $this->discount_amount,
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
