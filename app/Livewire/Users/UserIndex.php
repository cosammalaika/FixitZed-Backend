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
            session()->flash('success', "You cannot delete your own account.");
            return;
        }

        $user = User::find($id);

        if ($user) {
            $user->delete();
            session()->flash('success', "User deleted successfully.");
        } else {
            session()->flash('success', "User not found.");
        }
    }

}
