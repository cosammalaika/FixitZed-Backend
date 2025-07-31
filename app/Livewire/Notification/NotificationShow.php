<?php

namespace App\Livewire\Notification;

use App\Models\Notification;
use Livewire\Component;

class NotificationShow extends Component
{
    public $notification;

    public function mount($id)
    {
        $this->notification = Notification::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.notification.notification-show');
    }
}
