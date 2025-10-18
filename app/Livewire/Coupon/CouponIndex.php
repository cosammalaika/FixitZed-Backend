<?php

namespace App\Livewire\Coupon;

use App\Models\Coupon;
use Livewire\Component;

class CouponIndex extends Component
{
    protected $listeners = ['deleteCoupon' => 'delete'];

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

            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Coupon deleted successfully.',
            ]);
        } else {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Coupon not found.',
            ]);
        }
    }
}
