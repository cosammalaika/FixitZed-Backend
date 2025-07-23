<?php

namespace App\Livewire\Review;

use App\Models\Review;
use Livewire\Component;

class ReviewIndex extends Component
{
    public function render()
    {
        $reviews = Review::get();
        return view('livewire.review.review-index', compact("Review"));
    }
    public function delete($id)
    {
        $reviews = Review::find($id);

        $reviews->delete();
        session()->flash('success', "Review deleted successfully.");
        return view('livewire.review.review-index', compact("Review"));

    }
}
