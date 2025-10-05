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

            // Revert user type back to Customer if they are no longer a fixer
            if ($user) {
                $user->removeRole('Fixer');
                if (! $user->hasRole('Customer')) {
                    $user->assignRole('Customer');
                }
            }

            session()->flash('success', "Fixer deleted successfully.");
        }

        return redirect()->route('fixer.index');
    }
}
