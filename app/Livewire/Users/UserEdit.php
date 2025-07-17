<?php

namespace App\Livewire\Users;

use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserEdit extends Component
{
    public $user, $name, $email, $password, $confirm_password,$allRoles,$roles=[];

    public function mount($id)
    {
        $this->user = User::find($id); // safer to use findOrFail
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->allRoles = Role::all();
        $this->roles = $this->user->roles()->pluck("name");
    }

    public function render()
    {
        return view('livewire.users.user-edit');
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->user->id,
            'password' => 'nullable|same:confirm_password|min:6',
        ]);

        $this->user->name = $this->name;
        $this->user->email = $this->email;

        if ($this->password) {
            $this->user->password = Hash::make($this->password);
        }

        $this->user->save();

        $this->user->syncRoles($this->roles);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }
}
