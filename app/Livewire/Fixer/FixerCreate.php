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
    public $showServiceDropdown = false;
    public $serviceSearch = '';


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

        $this->showServiceDropdown = false;
        $this->serviceSearch = '';
    }

    public function render()
    {
        return view('livewire.fixer.fixer-create', [
            'users' => $this->users,
            'services' => $this->filteredServices,
            'totalServices' => $this->allServices?->count() ?? 0,
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
        if (! $this->showServiceDropdown) {
            $this->serviceSearch = '';
        }
    }

    public function getFilteredServicesProperty()
    {
        $term = strtolower(trim((string) $this->serviceSearch));
        if ($term === '') {
            return $this->allServices ?? collect();
        }

        return ($this->allServices ?? collect())->filter(function ($service) use ($term) {
            return str_contains(strtolower($service->name), $term);
        })->values();
    }
}
