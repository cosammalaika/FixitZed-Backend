<?php

namespace App\Livewire\LocationOption;

use App\Models\LocationOption;
use Livewire\Component;

class LocationOptionCreate extends Component
{
    public $name, $latitude, $longitude, $is_active = true;

    public function render()
    {
        return view('livewire.location-option.location-option-create');
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:191',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        LocationOption::create([
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_active' => (bool) $this->is_active,
        ]);

        session()->flash('success', 'Location created successfully.');
        $this->reset(['name', 'latitude', 'longitude', 'is_active']);
        $this->dispatch('location-created');
    }
}

