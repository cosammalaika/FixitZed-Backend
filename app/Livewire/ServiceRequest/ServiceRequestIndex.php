<?php

namespace App\Livewire\ServiceRequest;

use App\Models\ServiceRequest;
use Livewire\Component;

class ServiceRequestIndex extends Component
{
    public $serviceRequests;

    public function mount()
    {
        $this->fetchServiceRequests();
    }

    public function render()
    {
        return view('livewire.service-request.service-request-index', [
            'serviceRequests' => $this->serviceRequests
        ]);
    }

    public function delete($id)
    {
        $serviceRequest = ServiceRequest::find($id);

        if ($serviceRequest) {
            $serviceRequest->delete();
            log_user_action('deleted service request', "ServiceRequest ID: {$id}");
            session()->flash('success', 'Service Request deleted successfully.');
            $this->dispatch('flash-message-added');
        } else {
            session()->flash('error', 'Service Request not found.');
            $this->dispatch('flash-message-added');
        }

        $this->fetchServiceRequests();
    }


    private function fetchServiceRequests()
    {
        $this->serviceRequests = ServiceRequest::with([
            'customer',
            'fixer.user',
            'service'
        ])->latest()->get();
    }
}
