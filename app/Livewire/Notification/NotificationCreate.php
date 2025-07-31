<?php

namespace App\Livewire\Notification;

use App\Models\Notification;
use App\Models\User;
use Livewire\Component;

class NotificationCreate extends Component
{
    public $recipient_type = '',$user_id = null, $title = '',$message = '',$users = [];

    public function mount()
    {
        $this->users = User::where('status', 'Active')->get();
    }

    public function updatedRecipientType($value)
    {
        // reset user_id if not Individual
        if ($value !== 'Individual') {
            $this->user_id = null;
        }
    }

    public function submit()
    {
        $this->validate([
            'recipient_type' => 'required|in:Customer,Fixer,Admin,Support,Individual',
            'user_id' => 'nullable|required_if:recipient_type,Individual|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        Notification::create([
            'recipient_type' => $this->recipient_type,
            'user_id' => $this->recipient_type === 'Individual' ? $this->user_id : null,
            'title' => $this->title,
            'message' => $this->message,
        ]);

        session()->flash('success', 'Notification created successfully!');
        return redirect()->route('notification.index');
    }

    public function render()
    {
        return view('livewire.notification.notification-create');
    }
}
