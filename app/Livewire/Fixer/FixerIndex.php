<?php

namespace App\Livewire\Fixer;

use App\Models\Fixer;
use Livewire\Component;

class FixerIndex extends Component
{
    public function render()
    {
        $fixers = Fixer::get();
        return view('livewire.fixer.fixer-index', compact("Fixer"));
    }
    public function delete($id)
    {
        $fixers = Fixer::find($id);

        $fixers->delete();
        session()->flash('success', "Fixer deleted successfully.");
        return view('livewire.fixer.fixer-index', compact("Fixer"));

    }
}
