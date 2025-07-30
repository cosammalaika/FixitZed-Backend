<?php

namespace App\Livewire\ServiceRequest;

use App\Models\ServiceRequest;
use Livewire\Component;

class ServiceRequestShow extends Component
{
    public $serviceRequest;

    public function mount($id)
    {
        $this->serviceRequest = ServiceRequest::with(['customer', 'fixer.user', 'service'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.service-request.service-request-show');
    }
}
