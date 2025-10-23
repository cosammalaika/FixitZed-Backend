<?php

namespace App\Livewire\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Fixer;
use App\Models\Service;
use App\Support\ProvinceDistrict;
use Livewire\Component;

class ServiceRequestCreate extends Component
{
    public $customer_id, $fixer_id, $service_id, $scheduled_at, $status = 'pending';
    public $province = '';
    public $district = '';
    public array $provinceOptions = [];
    public array $districtOptions = [];
    public $customers, $services;
    public $filteredFixers = [];
    public array $provinceDistricts = [];

    public function mount()
    {
        $this->customers = User::role('Customer')
            ->where('status', 'Active')
            ->get();

        $this->services = Service::all();
        $this->filteredFixers = collect();

        $this->loadProvinceData();
    }

    protected function loadProvinceData(): void
    {
        $map = ProvinceDistrict::map();
        $this->provinceDistricts = $map;
        $this->provinceOptions = array_keys($map);
        $this->districtOptions = [];
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

    public function updatedProvince($value): void
    {
        $value = (string) $value;
        $this->province = $value;
        if (empty($this->provinceDistricts)) {
            $this->loadProvinceData();
        }
        $this->districtOptions = $this->provinceDistricts[$value] ?? [];
        if (! in_array($this->district, $this->districtOptions, true)) {
            $this->district = '';
        }
    }


    public function render()
    {
        return view('livewire.service-request.service-request-create', [
            'provinceMap' => $this->provinceDistricts,
        ]);
    }

    public function submit()
    {
        $this->validate([
            'customer_id' => 'required|exists:users,id',
            'fixer_id' => 'required|exists:fixers,id',
            'service_id' => 'required|exists:services,id',
            'scheduled_at' => 'required|date',
            'status' => 'required|in:pending,accepted,completed,cancelled',
            'province' => 'required|string',
            'district' => 'required|string',
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
            'location' => trim($this->province . ', ' . $this->district),
        ]);

        log_user_action('created service request', "ServiceRequest ID: {$request->id}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Service request created successfully.',
            'redirect' => route('serviceRequest.index'),
        ]);
    }
}
