<?php

namespace App\Livewire\Service;

use App\Models\Subcategory;
use Livewire\Component;
use App\Models\Service;

class ServiceEdit extends Component
{
    public $serviceId, $name, $description, $price, $is_active, $subcategories, $subcategory_id, $duration_minutes;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'nullable|numeric|min:0',
        'duration_minutes' => 'nullable|numeric|min:0',
        'is_active' => 'boolean',
    ];

    public function mount($id)
    {

        $service = Service::find($id);
        $this->serviceId = $service->id;
        $this->name = $service->name;
        $this->description = $service->description;
        $this->duration_minutes = $service->duration_minutes;
        $this->price = $service->price;
        $this->is_active = $service->is_active;
        $this->subcategory_id = $service->subcategory_id;
        $this->subcategories = Subcategory::all();
    }
    public function render()
    {
        return view('livewire.service.service-edit');
    }
    public function update()
    {
        $this->validate();

        $service = Service::findOrFail($this->serviceId);
        $oldName = $service->name;

        $service->update([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'subcategory_id' => $this->subcategory_id,
            'is_active' => $this->is_active,
        ]);

        log_user_action('updated service', "From '{$oldName}' to '{$this->name}', ID: {$service->id}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Service updated successfully.',
            'redirect' => route('services.index'),
        ]);
    }



}
