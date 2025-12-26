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
    public $allServices;
    public $selected_services = [];
    public $showServiceDropdown = false;

    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'bio' => 'nullable|string|max:1000',
        'status' => 'required|string|in:pending,approved,rejected',
        'selected_services' => 'array|min:1',
        'selected_services.*' => 'exists:services,id',
    ];

    public function mount($id)
    {
        $this->fixerId = $id;

        $fixer = Fixer::with('services')->findOrFail($id);

        $this->user_id = (string) $fixer->user_id;
        $this->bio = (string) ($fixer->bio ?? '');
        $this->status = strtolower((string) ($fixer->status ?? 'pending'));
        $this->selected_services = $fixer->services()
            ->pluck('services.id')
            ->unique()
            ->map(fn ($id) => (string) $id)
            ->toArray();

        // Allow selecting the current user or any user who isn't already a fixer
        $currentUserId = $this->user_id;
        $this->users = User::where('status', 'Active')
            ->where(function ($q) use ($currentUserId) {
                $q->whereDoesntHave('fixer')
                  ->orWhere('id', $currentUserId);
            })
            ->get();
        $this->allServices = Service::query()
            ->select('id', 'name')
            ->distinct()
            ->orderBy('name')
            ->get();
    }

    public function submit()
    {
        if (is_array($this->status)) {
            logger()->warning('FixerEdit status received as array', [
                'type' => gettype($this->status),
                'keys' => array_keys($this->status),
            ]);

            $firstScalar = collect($this->status)
                ->first(function ($value) {
                    return is_scalar($value) && trim((string) $value) !== '';
                });

            $this->status = $firstScalar ?? null;
        }

        if (is_scalar($this->status)) {
            $this->status = strtolower(trim((string) $this->status));
        }

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
        $this->selected_services = $fixer->services()
            ->pluck('services.id')
            ->unique()
            ->map(fn ($id) => (string) $id)
            ->toArray();

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

        $this->showServiceDropdown = false;
    }

    public function render()
    {
        return view('livewire.fixer.fixer-edit', [
            'users' => $this->users,
            'services' => $this->allServices,
        ]);
    }

    public function removeService($id): void
    {
        $this->selected_services = collect($this->selected_services ?? [])
            ->filter(fn ($sid) => (string) $sid !== (string) $id)
            ->values()
            ->toArray();
    }

    public function toggleService($id): void
    {
        $id = (string) $id;
        $current = collect($this->selected_services ?? []);
        if ($current->contains($id)) {
            $this->selected_services = $current->reject(fn ($sid) => $sid === $id)->values()->toArray();
        } else {
            $this->selected_services = $current->push($id)->unique()->values()->toArray();
        }
    }

    public function toggleServiceDropdown(): void
    {
        $this->showServiceDropdown = ! $this->showServiceDropdown;
    }
}
