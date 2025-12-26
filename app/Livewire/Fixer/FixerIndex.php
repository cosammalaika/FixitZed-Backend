<?php

namespace App\Livewire\Fixer;

use App\Models\Fixer;
use App\Services\FixerDeletionService;
use Livewire\Component;

class FixerIndex extends Component
{
    protected $listeners = ['deleteFixer' => 'delete'];

    public function render()
    {
        $fixers = Fixer::whereHas('user', function ($query) {
            $query->where('status', 'Active');
        })
            ->with(['user', 'services', 'wallet'])
            ->latest()
            ->get();

        return view('livewire.fixer.fixer-index', compact('fixers'));
    }

    public function delete($id, FixerDeletionService $deleter)
    {
        $fixer = Fixer::find($id);

        if (! $fixer) {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Fixer not found.',
            ]);

            return;
        }

        $deleter->deleteFixerAndUser((int) $id);

        log_user_action('deleted fixer', "Deleted Fixer ID: {$id}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Fixer deleted successfully.',
        ]);
    }
}
