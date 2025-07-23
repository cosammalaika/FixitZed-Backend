<?php

namespace App\Livewire\Payment;

use App\Models\Payment;
use Livewire\Component;

class PaymentIndex extends Component
{
    public function render()
    {
        $payments = Payment::get();
        return view('livewire.payment.payment-index', compact("Payment"));
    }
    public function delete($id)
    {
        $payments = Payment::find($id);

        $payments->delete();
        session()->flash('success', "Payment deleted successfully.");
        return view('livewire.payment.payment-index', compact("Payment"));

    }
}
