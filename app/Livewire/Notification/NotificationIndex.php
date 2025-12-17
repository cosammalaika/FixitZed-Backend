<?php

namespace App\Livewire\Notification;

use App\Models\Notification;
use App\Services\NotificationPruner;
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
        $this->notifications = Notification::all();
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

    public function pruneOldNotifications(NotificationPruner $pruner): void
    {
        abort_unless(auth()->user()?->can('edit.notifications'), 403);
        $deleted = $pruner->prune();
        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => "Pruned {$deleted} old notifications.",
        ]);
        $this->loadNotifications();
    }
    public function render()
    {
        return view('livewire.notification.notification-index');
    }
}
