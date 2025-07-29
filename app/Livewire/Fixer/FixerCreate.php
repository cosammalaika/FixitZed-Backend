<?php

namespace App\Livewire\Fixer;

use App\Models\Fixer;
use App\Models\User;
use Livewire\Component;

class FixerCreate extends Component
{
    public $user_id, $bio, $status = 'pending';
    public $users; // List of available users

    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'bio' => 'nullable|string|max:1000',
        'status' => 'required|in:pending,approved,rejected',
    ];

    public function mount()
    {
        $this->users = User::where('status', 'Active')->get(); 
    }

    public function submit()
    {
        $this->validate();

        Fixer::create([
            'user_id' => $this->user_id,
            'bio' => $this->bio,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Fixer created successfully.');
        return to_route('fixer.index')->with('success', 'Fixer created successfully.');
    }

    public function render()
    {
        return view('livewire.fixer.fixer-create', [
            'users' => $this->users,
        ]);
    }
}
