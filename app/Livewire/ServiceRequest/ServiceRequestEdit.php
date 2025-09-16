<?php

namespace App\Livewire\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Fixer;
use App\Models\Service;
use App\Models\Payment;
use Livewire\Component;

class ServiceRequestEdit extends Component
{
    public $serviceRequestId;
    public $customer_id, $fixer_id, $service_id, $scheduled_at, $status, $location;
    public $customers, $fixers, $services, $hasValidPayment = false;


    public function mount($id)
    {
        $this->serviceRequestId = $id;

        $serviceRequest = ServiceRequest::findOrFail($id);

        $this->customer_id = $serviceRequest->customer_id;
        $this->fixer_id = $serviceRequest->fixer_id;
        $this->service_id = $serviceRequest->service_id;
        $this->scheduled_at = $serviceRequest->scheduled_at;
        $this->status = $serviceRequest->status;
        $this->location = $serviceRequest->location;

        $this->customers = User::all();
        $this->fixers = Fixer::all();
        $this->services = Service::all();

        $this->hasValidPayment = Payment::where('service_request_id', $id)
            ->whereIn('status', ['accepted', 'completed']) // adjust based on your logic
            ->exists();
    }


    public function render()
    {
        return view('livewire.service-request.service-request-edit');
    }

    public function update()
    {
        $this->validate([
            'customer_id' => 'required|exists:users,id',
            'fixer_id' => 'required|exists:fixers,id',
            'service_id' => 'required|exists:services,id',
            'scheduled_at' => 'required|date',
            'status' => 'required|in:pending,accepted,completed,cancelled',
            'location' => 'nullable|string',
        ]);

        // Ensure the selected fixer is approved and linked to the selected service
        $isQualified = Fixer::where('id', $this->fixer_id)
            ->where('status', 'approved')
            ->whereHas('services', function ($q) {
                $q->where('services.id', $this->service_id);
            })
            ->exists();
        if (!$isQualified) {
            $this->addError('fixer_id', 'Selected fixer is not qualified for the chosen service.');
            return;
        }

        $serviceRequest = ServiceRequest::findOrFail($this->serviceRequestId);

        if ($this->status === 'completed') {
            $payment = Payment::where('service_request_id', $this->serviceRequestId)
                ->whereIn('status', ['accepted', 'completed'])
                ->first();

            if (!$payment) {
                session()->flash('error', 'Cannot mark as completed. Payment has not been made.');
                return;
            }
        }

        $serviceRequest->update([
            'customer_id' => $this->customer_id,
            'fixer_id' => $this->fixer_id,
            'service_id' => $this->service_id,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'location' => $this->location,
        ]);

        log_user_action('updated service request', "ServiceRequest ID: {$this->serviceRequestId}");

        session()->flash('success', 'Service request updated successfully.');
        return redirect()->route('serviceRequest.index');
    }

}
