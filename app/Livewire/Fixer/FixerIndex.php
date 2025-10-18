<?php

namespace App\Livewire\Fixer;

use App\Models\Fixer;
use Livewire\Component;

class FixerIndex extends Component
{
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

    public function delete($id)
    {
        $fixer = Fixer::find($id);

        if ($fixer) {
            $user = $fixer->user;
            $fixer->delete();

            log_user_action('deleted fixer', "Deleted Fixer ID: {$fixer->id}");

            if ($user) {
                $user->removeRole('Fixer');
                if (! $user->hasRole('Customer')) {
                    $user->assignRole('Customer');
                }
            }

            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Fixer deleted successfully.',
            ]);
        } else {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Fixer not found.',
            ]);
        }
    }
}
