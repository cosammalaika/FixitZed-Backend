<?php

namespace App\Livewire\Notification;

use App\Models\Notification;
use Livewire\Component;

class NotificationIndex extends Component
{
    public $notifications;
    protected $listeners = ['deleteNotification' => 'delete'];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = Notification::query()
            ->with('user')
            ->latest()
            ->get();
    }

    public function delete($id)
    {
        $notification = Notification::find($id);

        if ($notification) {
            $notification->delete();

            log_user_action('deleted notification', "Notification ID: {$notification->id}");

            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Notification deleted successfully.',
            ]);
            $this->loadNotifications();
        } else {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Notification not found.',
            ]);
        }
    }
    public function render()
    {
        return view('livewire.notification.notification-index');
    }
}
