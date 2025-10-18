<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;

class UserIndex extends Component
{
    public function render()
    {
        $users = User::get();
        return view('livewire.users.user-index', compact("users"));
    }
    public function delete($id)
    {
        // Prevent deleting currently logged-in user
        if ($id == auth()->id()) {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'warning',
                'message' => 'You cannot delete your own account.',
            ]);
            return;
        }

        $user = User::find($id);

        if ($user) {
            $user->delete();
            log_user_action('deleted user', description: "Deleted user ID: {$user->id}, Email: {$user->email}");

            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'User deleted successfully.',
            ]);
        } else {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'User not found.',
            ]);
        }
    }

}
