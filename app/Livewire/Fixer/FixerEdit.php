<?php

namespace App\Livewire\Fixer;

use App\Models\Fixer;
use App\Models\User;
use Livewire\Component;
use App\Models\Service;

class FixerEdit extends Component
{
    public $fixerId;
    public $user_id, $bio, $status;
    public $users;
    public $services;
    public $selected_services = [];

    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'bio' => 'nullable|string|max:1000',
        'status' => 'required|in:pending,approved,rejected',
        'selected_services' => 'array',
        'selected_services.*' => 'exists:services,id',
    ];

    public function mount($id)
    {
        $this->fixerId = $id;

        $fixer = Fixer::with('services')->findOrFail($id);

        $this->user_id = $fixer->user_id;
        $this->bio = $fixer->bio;
        $this->status = $fixer->status;
        $this->selected_services = $fixer->services->pluck('id')->toArray();

        $this->users = User::where('status', 'Active')->get();
        $this->services = Service::all();
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

        // Sync selected services (many-to-many)
        $fixer->services()->sync($this->selected_services);

        log_user_action('updated fixer', "Updated Fixer ID: {$fixer->id}");

        session()->flash('success', 'Fixer updated successfully.');
        return to_route('fixer.index');
    }

    public function render()
    {
        return view('livewire.fixer.fixer-edit', [
            'users' => $this->users,
            'services' => $this->services,
        ]);
    }
}
