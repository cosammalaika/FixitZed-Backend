<?php

namespace App\Livewire\Notification;

use App\Models\Notification;
use Livewire\Component;

class NotificationEdit extends Component
{
    public $notification;
    public $title, $message, $read;

    public function mount($id)
    {
        $this->notification = Notification::findOrFail($id);
        $this->title = $this->notification->title;
        $this->message = $this->notification->message;
        $this->read = $this->notification->read;
    }

    public function submit()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'read' => 'boolean',
        ]);

        $this->notification->update([
            'title' => $this->title,
            'message' => $this->message,
            'read' => $this->read,
        ]);

        log_user_action('updated notification', "Notification ID: {$this->notification->id}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Notification updated successfully!',
            'redirect' => route('notification.index'),
        ]);
    }

    public function render()
    {
        return view('livewire.notification.notification-edit');
    }
}
