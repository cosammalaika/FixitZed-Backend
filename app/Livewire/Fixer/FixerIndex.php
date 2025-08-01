<?php

namespace App\Livewire\Fixer;

use App\Models\Fixer;
use Livewire\Component;

class FixerIndex extends Component
{
    public function render()
    {
        $fixers = Fixer::whereHas('user', function ($query) {
            $query->where('status', 'Active');
        })->with('user')->latest()->get();

        return view('livewire.fixer.fixer-index', compact("fixers"));
    }

    public function delete($id)
    {
        $fixer = Fixer::find($id);

        if ($fixer) {
            $fixer->delete();

            log_user_action('deleted fixer', "Deleted Fixer ID: {$fixer->id}");

            session()->flash('success', "Fixer deleted successfully.");
        }

        return redirect()->route('fixer.index');
    }
}
