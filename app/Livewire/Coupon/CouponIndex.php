<?php

namespace App\Livewire\Coupon;

use App\Models\Coupon;
use Livewire\Component;

class CouponIndex extends Component
{
    public function render()
    {
        $coupons = Coupon::get();
        return view('livewire.coupon.coupon-index', compact("Coupon"));
    }
    public function delete($id)
    {
        $coupons = Coupon::find($id);

        $coupons->delete();
        session()->flash('success', "Coupon deleted successfully.");
        return view('livewire.coupon.coupon-index', compact("Coupon"));

    }
}
