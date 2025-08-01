<?php

namespace App\Livewire\Fixer;

use App\Models\Fixer;
use App\Models\Service;
use App\Models\User;
use Livewire\Component;

class FixerCreate extends Component
{
    public $user_id, $bio, $status = 'pending';
    public $users;
    public $selected_services = [];
    public $allServices;


    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'bio' => 'nullable|string|max:1000',
        'status' => 'required|in:pending,approved,rejected',
    ];

    public function mount()
    {
        $this->users = User::where('status', 'Active')->get();
        $this->allServices = Service::all();
    }

    public function submit()
    {
        $this->validate();

        $fixer = Fixer::create([
            'user_id' => $this->user_id,
            'bio' => $this->bio,
            'status' => $this->status,
        ]);

        $fixer->services()->sync($this->selected_services);

        log_user_action('created fixer', "Fixer ID: {$fixer->id}, User ID: {$this->user_id}");

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
