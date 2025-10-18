<?php

namespace App\Livewire\Review;

use App\Models\Review;
use Livewire\Component;

class ReviewIndex extends Component
{
    public function render()
    {
        $reviews = Review::get();
        return view('livewire.review.review-index', compact("reviews"));
    }
    public function delete($id)
    {
        $review = Review::find($id);

        if ($review) {
            $review->delete();
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Review deleted successfully.',
            ]);
        } else {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Review not found.',
            ]);
        }
    }
}
