<?php

namespace App\Livewire\Payment;

use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Support\Str;
use Livewire\Component;

class PaymentCreate extends Component
{
    public $service_request_id, $amount, $status = 'pending', $payment_method, $transaction_id, $paid_at, $serviceRequests;

    public function mount()
    {
        $this->serviceRequests = ServiceRequest::where('status', '!=', 'completed')
            ->with(['service', 'customer'])
            ->get();
    }

    public function render()
    {
        return view('livewire.payment.payment-create');
    }

    public function submit()
    {
        $this->validate([
            'service_request_id' => 'required|exists:service_requests,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,accepted,in_progress,completed,cancelled',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $this->transaction_id = 'FIZ-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5));
        $this->paid_at = now();

        Payment::create([
            'service_request_id' => $this->service_request_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'paid_at' => $this->paid_at,
        ]);

        
        if ($this->status === 'accepted') {
            $serviceRequest = ServiceRequest::find($this->service_request_id);
            if ($serviceRequest && $serviceRequest->status !== 'completed') {
                $serviceRequest->update(['status' => 'completed']);
            }
        }

        session()->flash('success', 'Payment created successfully.');
        return redirect()->route('payment.index');
    }
}
