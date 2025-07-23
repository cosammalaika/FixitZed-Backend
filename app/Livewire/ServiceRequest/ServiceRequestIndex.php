<?php

namespace App\Livewire\ServiceRequest;

use App\Models\ServiceRequest;
use Livewire\Component;

class ServiceRequestIndex extends Component
{
    public function render()
    {
        $serviceRequests = ServiceRequest::get();
        return view('livewire.service-request.service-request-index', compact("Service Request"));
    }
    public function delete($id)
    {
        $serviceRequests = ServiceRequest::find($id);

        $serviceRequests->delete();
        session()->flash('success', "Service Request deleted successfully.");
        return view('livewire.service-request.service-request-index', compact("Service Request"));

    }
}
