<?php

namespace App\Livewire\Coupon;

use Livewire\Component;

class CouponCreate extends Component
{
    public $code,$discount_percent,$valid_from,$valid_to,$usage_limit;
    protected $rules = [
        'code' => 'required|string|max:255',
        'discount_percent' => 'nullable|numeric',
        'valid_from' => 'nullable|numeric|min:0',
        'valid_to' => 'nullable|numeric|min:0',
        'usage_limit' => 'nullable|numeric|min:0',
    ];
    
    public function submit()
    {
        $this->validate();

        Coupon::create([
            'code' => $this->code,
            'discount_percent' => $this->discount_percent,
            'valid_from' => $this->valid_from,
            'valid_to' => $this->valid_to,
        ]);

        session()->flash('success', 'Service created successfully.');
        return to_route("coupon.index")->with("success", "Coupon created successfully");
    }
    public function render()
    {
        return view('livewire.coupon.coupon-create');
    }
}
