<?php

namespace App\Livewire\Rating;

use App\Models\Rating;
use Livewire\Component;

class RatingIndex extends Component
{
    public function render()
    {
        $ratings = Rating::get();
        return view('livewire.rating.rating-index', compact("Rating"));
    }
    public function delete($id)
    {
        $ratings = Rating::find($id);

        $ratings->delete();
        session()->flash('success', "Rating deleted successfully.");
        return view('livewire.rating.rating-index', compact("Rating"));

    }
   
}
