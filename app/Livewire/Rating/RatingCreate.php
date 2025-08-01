<?php
namespace App\Livewire\Rating;

use App\Models\Rating;
use App\Models\User;
use App\Models\ServiceRequest;
use Livewire\Component;

class RatingCreate extends Component
{
    public $rater_id;
    public $rated_user_id;
    public $service_request_id;
    public $role = 'customer';
    public $rating;
    public $comment;

    public $users;
    public $serviceRequests;

    public function mount()
    {
        $this->users = User::all();
        $this->serviceRequests = ServiceRequest::all();
    }

    public function submit()
    {
        $this->validate([
            'rater_id' => 'required|exists:users,id',
            'rated_user_id' => 'required|exists:users,id',
            'service_request_id' => 'required|exists:service_requests,id',
            'role' => 'required|in:customer,fixer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $rating = Rating::create([
            'rater_id' => $this->rater_id,
            'rated_user_id' => $this->rated_user_id,
            'service_request_id' => $this->service_request_id,
            'role' => $this->role,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);

        log_user_action('created rating', "Rating ID: {$rating->id}, Rating: {$this->rating}, Role: {$this->role}");

        session()->flash('success', 'Rating submitted successfully.');
        return redirect()->route('rating.index');
    }

    public function render()
    {
        return view('livewire.rating.rating-create');
    }
}
