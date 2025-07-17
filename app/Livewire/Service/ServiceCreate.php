<?php

namespace App\Livewire\Service;

use Livewire\Component;
use App\Models\Service;
class ServiceCreate extends Component
{
    public $name,$description, $base_price,$is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'base_price' => 'nullable|numeric|min:0',
        'is_active' => 'boolean',
    ];

    public function save()
    {
        $this->validate();

        Service::create([
            'name' => $this->name,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Service created successfully.');
        return to_route("services.index")->with("success","Service created successfully");
    }

    public function render()
    {
        return view('livewire.service.service-create');
    }
}