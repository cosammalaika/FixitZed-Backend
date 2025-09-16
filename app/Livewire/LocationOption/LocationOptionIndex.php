<?php

namespace App\Livewire\LocationOption;

use App\Models\LocationOption;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class LocationOptionIndex extends Component
{
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
            session()->flash('success', 'Location deleted successfully.');
        }
        return redirect()->route('location-options.index');
    }

    public function toggle($id)
    {
        $opt = LocationOption::find($id);
        if ($opt) {
            $opt->is_active = !$opt->is_active;
            $opt->save();
            session()->flash('success', 'Location status updated.');
        }
        return redirect()->route('location-options.index');
    }
}
