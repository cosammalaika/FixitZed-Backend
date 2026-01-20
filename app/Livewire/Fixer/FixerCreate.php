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
        'selected_services' => 'array',
        'selected_services.*' => 'exists:services,id',
    ];

    public function mount()
    {
        // Only show users who are not already fixers
        $this->users = User::where('status', 'Active')
            ->whereDoesntHave('fixer')
            ->get();
        $this->allServices = Service::query()
            ->select('id', 'name', 'subcategory_id')
            ->orderBy('name')
            ->get()
            ->unique(fn ($service) => strtolower($service->name) . '-' . $service->subcategory_id)
            ->values();
    }

    public function submit()
    {
        $this->status = strtolower(trim((string) $this->status));
        $this->validate();

        $fixer = Fixer::create([
            'user_id' => $this->user_id,
            'bio' => $this->bio,
            'status' => $this->status,
        ]);

        $services = collect($this->selected_services ?? [])
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $fixer->services()->sync($services);

        // Update the user's type to Fixer
        $user = User::find($this->user_id);
        if ($user) {
            if (! $user->hasRole('Fixer')) {
                $user->assignRole('Fixer');
            }
            if (! $user->hasRole('Customer')) {
                $user->assignRole('Customer');
            }
        }

        log_user_action('created fixer', "Fixer ID: {$fixer->id}, User ID: {$this->user_id}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Fixer created successfully.',
            'redirect' => route('fixer.index'),
        ]);
    }

    public function render()
    {
        return view('livewire.fixer.fixer-create', [
            'users' => $this->users,
        ]);
    }
}
