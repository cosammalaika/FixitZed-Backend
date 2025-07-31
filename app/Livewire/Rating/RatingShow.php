<?php

namespace App\Livewire\Rating;

use App\Models\Rating;
use Livewire\Component;

class RatingShow extends Component
{
    public $rating;

    public function mount($id)
    {
        $this->rating = Rating::with(['rater', 'ratedUser', 'serviceRequest'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.rating.rating-show');
    }
}
