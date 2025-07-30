<?php

namespace App\Livewire\Payment;

use App\Models\Payment;
use Livewire\Component;

class PaymentShow extends Component
{
    public $payment;

    public function mount($id)
    {
        $this->payment = Payment::with('serviceRequest')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.payment.payment-show');
    }
}
