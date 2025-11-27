<?php

namespace App\Livewire\Fixer;

use App\Models\Fixer;
use Livewire\Component;

class FixerShow extends Component
{
    public $fixerId;
    public $fixer;

    public function mount($id)
    {
        $this->fixerId = $id;
        $this->fixer = Fixer::with('user')->findOrFail($id);
        $this->fixer = Fixer::with(['user', 'services'])->findOrFail($id);

    }

    public function render()
    {
        return view('livewire.fixer.fixer-show');
    }

    public function approve(): void
    {
        $fixer = Fixer::with('user')->findOrFail($this->fixerId);
        $fixer->status = 'approved';
        $fixer->accepted_terms_at = $fixer->accepted_terms_at ?? now();
        $fixer->save();

        $user = $fixer->user;
        if ($user) {
            if (! $user->hasRole('Fixer')) {
                $user->assignRole('Fixer');
            }
            if (! $user->hasRole('Customer')) {
                $user->assignRole('Customer');
            }
        }

        log_user_action('approved fixer', "Approved Fixer ID: {$fixer->id}");

        $this->fixer = $fixer->load(['user', 'services']);
        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Fixer approved.',
        ]);
    }

    public function reject(): void
    {
        $fixer = Fixer::with('user')->findOrFail($this->fixerId);
        $fixer->status = 'rejected';
        $fixer->save();

        log_user_action('rejected fixer', "Rejected Fixer ID: {$fixer->id}");

        $this->fixer = $fixer->load(['user', 'services']);
        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Fixer rejected.',
        ]);
    }
}
