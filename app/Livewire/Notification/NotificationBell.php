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
        $this->notifications = Notification::where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('recipient_type', 'Individual')
                    ->where('user_id', $user->id);
            })->orWhere(function ($q) use ($user) {
                $q->where('recipient_type', $user->user_type); // <-- changed from getRoleNames()->first()
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
}
