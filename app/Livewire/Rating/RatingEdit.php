<?php

namespace App\Livewire\Rating;

use App\Models\Rating;
use App\Models\User;
use App\Models\ServiceRequest;
use Livewire\Component;

class RatingEdit extends Component
{
    public $ratingId, $rater_id, $rated_user_id, $service_request_id, $role,$rating, $comment, $users, $serviceRequests;

    public function mount($ratingId)
    {
        $rating = Rating::findOrFail($ratingId);

        $this->ratingId = $rating->id;
        $this->rater_id = $rating->rater_id;
        $this->rated_user_id = $rating->rated_user_id;
        $this->service_request_id = $rating->service_request_id;
        $this->role = $rating->role;
        $this->rating = $rating->rating;
        $this->comment = $rating->comment;

        $this->users = User::all();
        $this->serviceRequests = ServiceRequest::all();
    }

    public function update()
    {
        $this->validate([
            'rater_id' => 'required|exists:users,id',
            'rated_user_id' => 'required|exists:users,id',
            'service_request_id' => 'required|exists:service_requests,id',
            'role' => 'required|in:customer,fixer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        Rating::findOrFail($this->ratingId)->update([
            'rater_id' => $this->rater_id,
            'rated_user_id' => $this->rated_user_id,
            'service_request_id' => $this->service_request_id,
            'role' => $this->role,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);

        session()->flash('success', 'Rating updated successfully.');

        return redirect()->route('ratings.index');
    }

    public function render()
    {
        return view('livewire.rating.rating-edit');
    }
}
