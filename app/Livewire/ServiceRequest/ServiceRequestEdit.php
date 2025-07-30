<?php

namespace App\Livewire\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Fixer;
use App\Models\Service;
use Livewire\Component;

class ServiceRequestEdit extends Component
{
    public $id;
    public $customer_id;
    public $fixer_id;
    public $service_id;
    public $scheduled_at;
    public $status;
    public $location;

    public $customers;
    public $fixers;
    public $services;

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

        $serviceRequest = ServiceRequest::findOrFail($this->id);

        $serviceRequest->update([
            'customer_id' => $this->customer_id,
            'fixer_id' => $this->fixer_id,
            'service_id' => $this->service_id,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'location' => $this->location,
        ]);

        session()->flash('success', 'Service request updated successfully.');

        return redirect()->route('serviceRequest.index');
    }
}
