<?php

namespace App\Livewire\Earning;

use App\Models\Earning;
use Livewire\Component;

class EarningIndex extends Component
{
    public function render()
    {
        return view('livewire.earning.earning-index', [
            'earnings' => Earning::latest()->with(['fixer.user', 'serviceRequest'])->get()
        ]);
    }

    public function delete($id)
    {
        $earnings = Earning::find($id);

        $earnings->delete();
        session()->flash('success', "Earning deleted successfully.");
        return view('livewire.earning.earning-index', compact("earnings"));

    }
}
