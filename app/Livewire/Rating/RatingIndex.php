<?php

namespace App\Livewire\Rating;

use App\Models\Rating;
use Livewire\Component;

class RatingIndex extends Component
{
    public function render()
    {
        $ratings = Rating::get();
        return view('livewire.rating.rating-index', compact("ratings"));
    }
    public function delete($id)
    {
        $rating = Rating::find($id);

        if ($rating) {
            $rating->delete();
            log_user_action('deleted rating', "Rating ID: {$id}");
            session()->flash('success', "Rating deleted successfully.");
        } else {
            session()->flash('error', "Rating not found.");
        }

        $ratings = Rating::get();
        return view('livewire.rating.rating-index', compact("ratings"));
    }

}
