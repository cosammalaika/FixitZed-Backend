<?php

namespace App\Livewire\Service;

use Livewire\Component;
use App\Models\Service;

class ServiceEdit extends Component
{
    public $serviceId, $name, $description, $base_price, $is_active;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'base_price' => 'nullable|numeric|min:0',
        'is_active' => 'boolean',
    ];

    public function mount($id)
    {
        $service = Service::find($id);
        $this->serviceId = $service->id;
        $this->name = $service->name;
        $this->description = $service->description;
        $this->base_price = $service->base_price;
        $this->is_active = $service->is_active;
    }
    public function render()
    {
        return view('livewire.service.service-edit');
    }
    public function update()
    {
        $this->validate();

        $service = Service::findOrFail($this->serviceId);
        $service->update([
            'name' => $this->name,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Service updated successfully.');
        return to_route("services.index")->with("success","Service created successfully");
    }


}
