<?php

namespace App\Livewire\Earning;

use App\Models\Earning;
use Livewire\Component;

class EarningIndex extends Component
{
    public function render()
    {
        $earnings = Earning::get();
        return view('livewire.earning.earning-index', compact("Earning"));
    }
    public function delete($id)
    {
        $earnings = Earning::find($id);

        $earnings->delete();
        session()->flash('success', "Earning deleted successfully.");
        return view('livewire.earning.earning-index', compact("Earning"));

    }
}
