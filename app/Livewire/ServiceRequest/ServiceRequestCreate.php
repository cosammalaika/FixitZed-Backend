<?php

namespace App\Livewire\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Fixer;
use App\Models\Service;
use App\Models\LocationOption;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Illuminate\Validation\ValidationException;

class ServiceRequestCreate extends Component
{
    public $customer_id, $fixer_id, $service_id, $scheduled_at, $status = 'pending', $location;
    public $location_option_id;
    public $locationOptions = [];
    public $customers, $services;
    public $filteredFixers = [];

    public function mount()
    {
        $this->customers = User::role('Customer')
            ->where('status', 'Active')
            ->get();

        $this->services = Service::all();
        $this->filteredFixers = collect();

        if (Schema::hasTable('location_options')) {
            $this->locationOptions = LocationOption::where('is_active', true)->orderBy('name')->get();
        }
    }


    public function updatedServiceId($value)
    {
        if ($value) {
            $service = Service::find($value);
            if ($service) {
                $this->filteredFixers = $service->fixers()->with('user')->get();
            } else {
                $this->filteredFixers = collect();
            }
        } else {
            $this->filteredFixers = collect();
        }
        $this->fixer_id = '';
    }

    public function updatedLocationOptionId($value)
    {
        if ($value) {
            $opt = LocationOption::where('id', $value)->where('is_active', true)->first();
            if ($opt) {
                $this->location = $opt->name;
            }
        }
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

        // Enforce that the selected fixer is approved AND linked to the selected service
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

        $request = ServiceRequest::create([
            'customer_id' => $this->customer_id,
            'fixer_id' => $this->fixer_id,
            'service_id' => $this->service_id,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'location' => $this->location,
        ]);

        log_user_action('created service request', "ServiceRequest ID: {$request->id}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Service request created successfully.',
            'redirect' => route('serviceRequest.index'),
        ]);
    }
}
