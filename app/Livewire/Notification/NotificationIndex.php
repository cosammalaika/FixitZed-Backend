<?php

namespace App\Livewire\Notification;

use App\Models\Notification;
use Livewire\Component;

class NotificationIndex extends Component
{
    public $notifications;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = Notification::all();
    }

    public function delete($id)
    {
        $notification = Notification::find($id);

        if ($notification) {
            $notification->delete();

            log_user_action('deleted notification', "Notification ID: {$notification->id}");

            session()->flash('success', "Notification deleted successfully.");
            $this->loadNotifications();
        } else {
            session()->flash('error', "Notification not found.");
        }
    }
    public function render()
    {
        return view('livewire.notification.notification-index');
    }
}
