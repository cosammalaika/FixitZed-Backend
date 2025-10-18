<?php

namespace App\Livewire\LocationOption;

use App\Models\LocationOption;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class LocationOptionIndex extends Component
{
    protected $listeners = ['deleteLocationConfirmed' => 'delete'];

    public function render()
    {
        $options = Schema::hasTable('location_options')
            ? LocationOption::orderBy('name')->get()
            : collect();
        return view('livewire.location-option.location-option-index', compact('options'));
    }

    public function delete($id)
    {
        $opt = LocationOption::find($id);
        if ($opt) {
            $opt->delete();
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Location deleted successfully.',
                'redirect' => route('location-options.index'),
            ]);
        }
    }

    public function toggle($id)
    {
        $opt = LocationOption::find($id);
        if ($opt) {
            $opt->is_active = !$opt->is_active;
            $opt->save();
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Location status updated.',
                'redirect' => route('location-options.index'),
            ]);
        }
    }
}
