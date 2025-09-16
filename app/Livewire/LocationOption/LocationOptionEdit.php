<?php

namespace App\Livewire\LocationOption;

use App\Models\LocationOption;
use Livewire\Component;

class LocationOptionEdit extends Component
{
    public $id;
    public $name, $latitude, $longitude, $is_active = true;

    public function mount($id)
    {
        $opt = LocationOption::findOrFail($id);
        $this->id = $opt->id;
        $this->name = $opt->name;
        $this->latitude = $opt->latitude;
        $this->longitude = $opt->longitude;
        $this->is_active = $opt->is_active;
    }

    public function render()
    {
        return view('livewire.location-option.location-option-edit');
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:191',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        $opt = LocationOption::findOrFail($this->id);
        $opt->update([
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_active' => (bool) $this->is_active,
        ]);

        session()->flash('success', 'Location updated successfully.');
        $this->dispatch('location-updated');
    }
}

