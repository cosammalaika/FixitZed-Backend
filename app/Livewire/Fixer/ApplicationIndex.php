<?php

namespace App\Livewire\Fixer;

use App\Models\Fixer;
use App\Models\Notification;
use Livewire\Component;

class ApplicationIndex extends Component
{
    public int $pendingCount = 0;

    public function mount(): void
    {
        $this->pendingCount = Fixer::where('status', 'pending')->count();
    }

    public function render()
    {
        $applications = Fixer::where('status', 'pending')
            ->with(['user', 'services'])
            ->latest()
            ->get();

        $currentCount = $applications->count();
        if ($currentCount > $this->pendingCount) {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'info',
                'message' => 'New fixer application received.',
                'toast' => true,
                'position' => 'top-end',
                'timer' => 4000,
            ]);
        }
        $this->pendingCount = $currentCount;

        return view('livewire.fixer.application-index', compact('applications'));
    }

    public function approve($id): void
    {
        $fixer = Fixer::with('user')->findOrFail($id);
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

            Notification::create([
                'recipient_type' => 'Individual',
                'user_id' => $user->id,
                'title' => 'Application approved',
                'message' => 'Your fixer application has been approved. You can now take jobs.',
                'read' => false,
            ]);
        }

        log_user_action('approved fixer', "Approved Fixer ID: {$fixer->id}");
        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Fixer approved.',
        ]);
    }

    public function reject($id): void
    {
        $fixer = Fixer::findOrFail($id);
        $fixer->status = 'rejected';
        $fixer->save();

        log_user_action('rejected fixer', "Rejected Fixer ID: {$fixer->id}");
        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Fixer rejected.',
        ]);
    }
}
