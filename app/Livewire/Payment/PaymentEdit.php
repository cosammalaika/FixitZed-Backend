<?php

namespace App\Livewire\Payment;

use App\Models\Payment;
use App\Models\ServiceRequest;
use Livewire\Component;

class PaymentEdit extends Component
{
    public $payment;
    public $service_request_id;
    public $amount;
    public $status;
    public $payment_method;
    public $transaction_id;
    public $paid_at;

    public $serviceRequests;

    public function mount($id)
    {
        $this->payment = Payment::findOrFail($id);
        $this->service_request_id = $this->payment->service_request_id;
        $this->amount = $this->payment->amount;
        $this->status = $this->payment->status;
        $this->payment_method = $this->payment->payment_method;
        $this->transaction_id = $this->payment->transaction_id;
        $this->paid_at = $this->payment->paid_at;

        $this->serviceRequests = ServiceRequest::all();
    }

    public function render()
    {
        return view('livewire.payment.payment-edit');
    }

    public function update()
    {
        $this->validate([
            'service_request_id' => 'required|exists:service_requests,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,accepted,in_progress,completed,cancelled',
            'payment_method' => 'nullable|string',
        ]);

        $this->payment->update([
            'service_request_id' => $this->service_request_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
        ]);

        log_user_action('updated payment', "Payment ID: {$this->payment->id}, Status: {$this->status}");

        if ($this->status === 'accepted') {
            $serviceRequest = ServiceRequest::find($this->service_request_id);
            if ($serviceRequest && $serviceRequest->status !== 'completed') {
                $serviceRequest->update(['status' => 'completed']);
            }
        }

        session()->flash('success', 'Payment updated successfully.');
        return redirect()->route('payment.index');
    }
}
