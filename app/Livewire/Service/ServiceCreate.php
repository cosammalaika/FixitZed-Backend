<?php

namespace App\Livewire\Service;

use Livewire\Component;
use App\Models\Service;
use App\Models\Subcategory;
class ServiceCreate extends Component
{
    public $name, $description, $price, $is_active = true, $subcategory_id, $duration_minutes;
    public $subcategories;
    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'nullable|numeric|min:0',
        'duration_minutes' => 'nullable|numeric|min:0',
        'subcategory_id' => 'required|exists:subcategories,id',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        $this->subcategories = Subcategory::all();
    }

    public function submit()
    {
        $this->validate();

        $service = Service::create([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'subcategory_id' => $this->subcategory_id,
            'is_active' => $this->is_active == "1" ? true : false,
        ]);

        log_user_action('created service', "Service: {$service->name}, ID: {$service->id}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Service created successfully.',
            'redirect' => route('services.index'),
        ]);
    }


    public function render()
    {
        return view('livewire.service.service-create', [
            'subcategories' => $this->subcategories,
        ]);
    }
}
