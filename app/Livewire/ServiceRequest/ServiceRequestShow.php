<?php

namespace App\Livewire\ServiceRequest;

use App\Jobs\NotifyCustomerNoFixerJob;
use App\Models\ServiceRequest;
use Livewire\Component;

class ServiceRequestShow extends Component
{
    public $serviceRequest;

    public function mount($id)
    {
        $this->serviceRequest = ServiceRequest::with(['customer', 'fixer.user', 'service'])->findOrFail($id);

        if ($this->shouldNotifyNoFixer($this->serviceRequest)) {
            NotifyCustomerNoFixerJob::dispatchIfNeeded($this->serviceRequest, 0);
        }
    }

    public function render()
    {
        return view('livewire.service-request.service-request-show');
    }

    protected function shouldNotifyNoFixer(ServiceRequest $serviceRequest): bool
    {
        if (! $serviceRequest->customer_id || $serviceRequest->no_fixer_notified_at !== null) {
            return false;
        }

        if ($serviceRequest->status !== 'pending') {
            return false;
        }

        return ! $serviceRequest->fixer || ! $serviceRequest->fixer->user;
    }
}
