<?php

namespace App\Livewire\Fixer;

use App\Models\Fixer;
use App\Models\User;
use Livewire\Component;

class FixerEdit extends Component
{
    public $fixerId;
    public $user_id, $bio, $status;
    public $users;

    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'bio' => 'nullable|string|max:1000',
        'status' => 'required|in:pending,approved,rejected',
    ];

    public function mount($id)
    {
        $this->fixerId = $id;

        $fixer = Fixer::findOrFail($id);

        $this->user_id = $fixer->user_id;
        $this->bio = $fixer->bio;
        $this->status = $fixer->status;

        $this->users = User::where('status', 'Active')->get();
    }

   public function submit()
{
    $this->validate();

    $fixer = Fixer::findOrFail($this->fixerId);

    $fixer->update([
        'user_id' => $this->user_id,
        'bio' => $this->bio,
        'status' => $this->status,
    ]);

    log_user_action('updated fixer', "Updated Fixer ID: {$fixer->id}");

    session()->flash('success', 'Fixer updated successfully.');
    return to_route('fixer.index')->with('success', 'Fixer updated successfully.');
}

    public function render()
    {
        return view('livewire.fixer.fixer-edit', [
            'users' => $this->users,
        ]);
    }
}
