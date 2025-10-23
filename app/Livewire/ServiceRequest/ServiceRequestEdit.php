<?php

namespace App\Livewire\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Fixer;
use App\Models\Service;
use App\Models\Payment;
use App\Support\ProvinceDistrict;
use Livewire\Component;

class ServiceRequestEdit extends Component
{
    public $serviceRequestId;
    public $customer_id, $fixer_id, $service_id, $scheduled_at, $status;
    public $province = '';
    public $district = '';
    public array $provinceOptions = [];
    public array $districtOptions = [];
    public $customers, $fixers, $services, $hasValidPayment = false;
    public array $provinceDistricts = [];


    public function mount($id)
    {
        $this->serviceRequestId = $id;

        $serviceRequest = ServiceRequest::findOrFail($id);

        $this->customer_id = $serviceRequest->customer_id;
        $this->fixer_id = $serviceRequest->fixer_id;
        $this->service_id = $serviceRequest->service_id;
        $this->scheduled_at = $serviceRequest->scheduled_at;
        $this->status = $serviceRequest->status;
        $this->hydrateLocation($serviceRequest->location);

        $this->customers = User::all();
        $this->fixers = Fixer::all();
        $this->services = Service::all();

        $this->hasValidPayment = Payment::where('service_request_id', $id)
            ->whereIn('status', ['accepted', 'completed']) // adjust based on your logic
            ->exists();

        $this->loadProvinceData();
    }

    protected function loadProvinceData(): void
    {
        $map = ProvinceDistrict::map();
        $this->provinceDistricts = $map;
        $this->provinceOptions = array_keys($map);
        $this->districtOptions = $map[$this->province] ?? [];
        if (! in_array($this->district, $this->districtOptions, true)) {
            $this->district = '';
        }
    }

    protected function hydrateLocation(?string $location): void
    {
        if (! $location) {
            $this->province = '';
            $this->district = '';
            return;
        }

        $parts = array_map(
            static fn ($segment) => trim($segment),
            explode(',', $location, 2)
        );

        $this->province = $parts[0] ?? '';
        $this->district = $parts[1] ?? '';
    }

    public function render()
    {
        return view('livewire.service-request.service-request-edit', [
            'provinceMap' => $this->provinceDistricts,
        ]);
    }

    public function update()
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
                $this->dispatchBrowserEvent('flash-message', [
                    'type' => 'error',
                    'message' => 'Cannot mark as completed. Payment has not been made.',
                ]);
                return;
            }
        }

        $serviceRequest->update([
            'customer_id' => $this->customer_id,
            'fixer_id' => $this->fixer_id,
            'service_id' => $this->service_id,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'location' => trim($this->province . ', ' . $this->district),
        ]);

        log_user_action('updated service request', "ServiceRequest ID: {$this->serviceRequestId}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Service request updated successfully.',
            'redirect' => route('serviceRequest.index'),
        ]);
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

}
