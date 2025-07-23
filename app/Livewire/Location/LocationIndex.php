<?php

namespace App\Livewire\Location;

use App\Models\Location;
use Livewire\Component;

class LocationIndex extends Component
{
    public function render()
    {
        $locations = Location::get();
        return view('livewire.location.location-index', compact("Location"));
    }
    public function delete($id)
    {
        $locations = Location::find($id);

        $locations->delete();
        session()->flash('success', "Location deleted successfully.");
        return view('livewire.location.location-index', compact("Location"));

    }
}
