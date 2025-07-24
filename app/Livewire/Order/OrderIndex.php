<?php

namespace App\Livewire\Order;

use App\Models\Order;
use Livewire\Component;

class OrderIndex extends Component
{
    public function render()
    {
        $orders = Order::get();
        return view('livewire.order.order-index', compact("orders"));
    }
    public function delete($id)
    {
        $orders = Order::find($id);

        $orders->delete();
        session()->flash('success', "Order deleted successfully.");
        return view('livewire.order.order-index', compact("orders"));

    }
    
}
