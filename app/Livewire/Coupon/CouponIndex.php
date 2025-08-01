<?php

namespace App\Livewire\Coupon;

use App\Models\Coupon;
use Livewire\Component;

class CouponIndex extends Component
{
    public function render()
    {
        $coupons = Coupon::get();
        return view('livewire.coupon.coupon-index', compact("coupons"));
    }
    public function delete($id)
    {
        $coupon = Coupon::find($id);

        if ($coupon) {
            $coupon->delete();

            log_user_action('deleted coupon', "Deleted coupon ID: {$coupon->id}, Code: {$coupon->code}");

            session()->flash('success', "Coupon deleted successfully.");
        } else {
            session()->flash('success', "Coupon not found.");
        }

        return redirect()->route('coupon.index');
    }
}
