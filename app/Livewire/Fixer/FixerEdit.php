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
        $this->status = strtolower($fixer->status ?? 'pending');
        $this->selected_services = $fixer->services->pluck('id')->toArray();

        // Allow selecting the current user or any user who isn't already a fixer
        $currentUserId = $this->user_id;
        $this->users = User::where('status', 'Active')
            ->where(function ($q) use ($currentUserId) {
                $q->whereDoesntHave('fixer')
                  ->orWhere('id', $currentUserId);
            })
            ->get();
        $this->services = Service::all();
    }

    public function submit()
    {
        $this->validate();

        $fixer = Fixer::findOrFail($this->fixerId);
        $originalUserId = $fixer->user_id;

        $fixer->update([
            'user_id' => $this->user_id,
            'bio' => $this->bio,
            'status' => $this->status,
        ]);

        // Sync selected services (many-to-many)
        $fixer->services()->sync($this->selected_services);

        // If the assigned user changed, update user types accordingly
        if ($originalUserId != $this->user_id) {
            $oldUser = User::find($originalUserId);
            if ($oldUser) {
                $oldUser->removeRole('Fixer');
                if (! $oldUser->hasRole('Customer')) {
                    $oldUser->assignRole('Customer');
                }
            }

            $newUser = User::find($this->user_id);
            if ($newUser) {
                if (! $newUser->hasRole('Fixer')) {
                    $newUser->assignRole('Fixer');
                }
                if (! $newUser->hasRole('Customer')) {
                    $newUser->assignRole('Customer');
                }
            }
        }

        log_user_action('updated fixer', "Updated Fixer ID: {$fixer->id}");
        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Fixer updated successfully.',
            'redirect' => route('fixer.index'),
        ]);
    }

    public function render()
    {
        return view('livewire.fixer.fixer-edit', [
            'users' => $this->users,
            'services' => $this->services,
        ]);
    }
}
