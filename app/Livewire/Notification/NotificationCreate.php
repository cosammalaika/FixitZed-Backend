<?php

namespace App\Livewire\Notification;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

class NotificationCreate extends Component
{
    public $recipient_type = '', $user_id = null, $title = '', $message = '';
    public $users = [];

    public function mount()
    {
        $this->users = User::query()
            ->select(['id', 'first_name', 'last_name', 'username', 'email', 'status'])
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereIn('status', ['Active', 'active', 'Approved', 'approved']);
            })
            ->get()
            ->map(function (User $user) {
                $parts = array_filter([
                    trim((string) ($user->first_name ?? '')),
                    trim((string) ($user->last_name ?? '')),
                ]);
                $display = trim(implode(' ', $parts));
                if ($display === '') {
                    $display = trim((string) ($user->username ?? ''));
                }
                if ($display === '') {
                    $display = trim((string) ($user->email ?? ''));
                }
                $user->display_name = $display;

                return $user;
            })
            ->sortBy(fn (User $user) => strtolower($user->display_name ?? ''))
            ->values()
            ->all();
    }

    public function updatedRecipientType($value)
    {
        $normalized = $this->normalizeRecipientType($value);
        $this->recipient_type = $normalized;

        // reset user_id if not Individual
        if ($normalized !== 'Individual') {
            $this->user_id = null;
        }
    }

    public function submit()
    {
        $this->recipient_type = $this->normalizeRecipientType($this->recipient_type);
        $recipientType = $this->recipient_type;

        $this->validate([
            'recipient_type' => 'required|in:Customer,Fixer,Admin,Support,Individual',
            'user_id' => 'nullable|required_if:recipient_type,Individual|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $notification = Notification::create([
            'recipient_type' => $recipientType,
            'user_id' => $recipientType === 'Individual' ? (int) $this->user_id : null,
            'title' => $this->title,
            'message' => $this->message,
        ]);

        log_user_action('created notification', "Notification ID: {$notification->id}, Title: {$this->title}");

        session()->flash('success', 'Notification created successfully!');
        return redirect()->route('notification.index');
    }

    public function render()
    {
        return view('livewire.notification.notification-create');
    }

    private function normalizeRecipientType($value): string
    {
        $trimmed = is_string($value) ? trim($value) : '';
        if ($trimmed === '') {
            return '';
        }

        $normalized = Str::title(Str::lower($trimmed));

        return match ($normalized) {
            'Customer' => 'Customer',
            'Fixer' => 'Fixer',
            'Admin' => 'Admin',
            'Support' => 'Support',
            'Individual' => 'Individual',
            default => $trimmed,
        };
    }
}
