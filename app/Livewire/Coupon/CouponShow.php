<?php

namespace App\Livewire\Coupon;

use App\Models\Coupon;
use Livewire\Component;

class CouponShow extends Component
{
    public $coupon;

    public function mount(Coupon $coupon)
    {
        $this->coupon = $coupon;
    }

    public function render()
    {
        return view('livewire.coupon.coupon-show');
    }
}
