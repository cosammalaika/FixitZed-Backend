<?php

namespace App\Livewire\Notification;

use App\Models\Notification;
use Livewire\Component;

class NotificationIndex extends Component
{
    public function render()
    {
        $notifications = Notification::get();
        return view('livewire.notification.notification-index', compact("Notification"));
    }
    public function delete($id)
    {
        $notifications = Notification::find($id);

        $notifications->delete();
        session()->flash('success', "Notification deleted successfully.");
        return view('livewire.notification.notification-index', compact("Notification"));

    }
}
