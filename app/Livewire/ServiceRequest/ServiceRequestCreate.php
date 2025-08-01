<?php

namespace App\Livewire\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Fixer;
use App\Models\Service;
use Livewire\Component;

class ServiceRequestCreate extends Component
{
    public $customer_id, $fixer_id, $service_id, $scheduled_at, $status = 'pending', $location;
    public $customers, $services;
    public $filteredFixers = [];

    public function mount()
    {
        $this->customers = User::where('user_type', 'Customer')
            ->where('status', 'Active')
            ->get();

        $this->services = Service::all();
        $this->filteredFixers = collect();
    }


public function updatedServiceId($value)
{
    if ($value) {
        $service = Service::find($value);
        if ($service) {
            $fixersQuery = $service->fixers()
                ->where('status', 'approved')
                ->with('user');

            $this->filteredFixers = $fixersQuery->get();

            logger('Filtered fixers:', $this->filteredFixers->toArray());  // Logs to Laravel log
        } else {
            $this->filteredFixers = collect();
        }
    } else {
        $this->filteredFixers = collect();
    }
    $this->fixer_id = '';
}


    public function render()
    {
        return view('livewire.service-request.service-request-create');
    }

    public function submit()
    {
        $this->validate([
            'customer_id' => 'required|exists:users,id',
            'fixer_id' => 'required|exists:fixers,id',
            'service_id' => 'required|exists:services,id',
            'scheduled_at' => 'required|date',
            'status' => 'required|in:pending,accepted,completed,cancelled',
            'location' => 'nullable|string',
        ]);

        $request = ServiceRequest::create([
            'customer_id' => $this->customer_id,
            'fixer_id' => $this->fixer_id,
            'service_id' => $this->service_id,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'location' => $this->location,
        ]);

        log_user_action('created service request', "ServiceRequest ID: {$request->id}");

        session()->flash('success', 'Service request created successfully.');

        return redirect()->route('serviceRequest.index');
    }
}
