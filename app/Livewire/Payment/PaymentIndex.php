<?php

namespace App\Livewire\Payment;

use App\Models\Payment;
use Livewire\Component;

class PaymentIndex extends Component
{
    protected $listeners = ['deletePayment' => 'delete'];

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

            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Payment deleted successfully.',
            ]);
        } else {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Payment not found.',
            ]);
        }
    }
}
