<?php

namespace App\Livewire\Payment;

use App\Models\Payment;
use Livewire\Component;

class PaymentIndex extends Component
{
    public function render()
    {
        $payments = Payment::get();
        return view('livewire.payment.payment-index', [
            'payments' => Payment::with('serviceRequest.service')->latest()->get()
        ]);
    }
    public function delete($id)
    {
        $payment = Payment::find($id);

        if ($payment) {
            $payment->delete();

            log_user_action('deleted payment', "Payment ID: {$id}");

            session()->flash('success', "Payment deleted successfully.");
        } else {
            session()->flash('error', "Payment not found.");
        }

        return view('livewire.payment.payment-index', [
            'payments' => Payment::with('serviceRequest.service')->latest()->get()
        ]);
    }
}
