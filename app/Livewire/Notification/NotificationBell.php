<?php

namespace App\Livewire\Notification;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;

    public function mount()
    {
        $user = Auth::user();
        $audiences = $this->audiencesForUser($user);

        $this->notifications = Notification::where(function ($query) use ($user, $audiences) {
            $query->where(function ($q) use ($user) {
                $q->where('recipient_type', 'Individual')
                    ->where('user_id', $user->id);
            })->orWhere(function ($q) use ($audiences) {
                if (empty($audiences)) {
                    return;
                }
                $q->whereIn('recipient_type', $audiences);
            });
        })
            ->latest()
            ->take(2)
            ->get();


        $this->unreadCount = $this->notifications->where('read', false)->count();
    }

    public function render()
    {
        return view('livewire.notification.notification-bell');
    }

    private function audiencesForUser($user): array
    {
        $roles = collect($user?->getRoleNames() ?? [])
            ->filter()
            ->map(fn ($role) => trim($role));

        if ($roles->isEmpty()) {
            return [];
        }

        return $roles->flatMap(function ($role) {
            $normalized = ucfirst(strtolower($role));
            return [
                $role,
                $normalized,
                strtoupper($role),
                strtolower($role),
            ];
        })->push('All')
          ->unique()
          ->values()
          ->all();
    }
}
