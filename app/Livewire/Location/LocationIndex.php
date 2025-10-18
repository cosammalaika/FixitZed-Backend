<?php

namespace App\Livewire\Location;

use App\Models\Location;
use Livewire\Component;

class LocationIndex extends Component
{
    public function render()
    {
        $locations = Location::get();
        return view('livewire.location.location-index', compact("locations"));
    }
    public function delete($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Location deleted successfully.',
        ]);
    }
}
