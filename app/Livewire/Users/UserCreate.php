<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserCreate extends Component
{
    public $first_name, $last_name, $email, $password, $confirm_password, $username, $contact_number, $user_type = 'user', $status = 'Active', $address;
    public $allRoles, $roles = [];


    public function mount()
    {
        $this->allRoles = Role::all();
    }

    public function render()
    {
        return view('livewire.users.user-create');
    }

    public function submit()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|string|max:20',
            'user_type' => 'required|in:Customer,Fixer,Admin,Support',
            'status' => 'required|in:Active,Inactive',
            'address' => 'nullable|string|max:1000',
            'roles' => 'nullable',
            'password' => 'required|same:confirm_password|min:6'
        ]);


        $user = User::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'user_type' => $this->user_type,
            'status' => $this->status,
            'address' => $this->address,
            'password' => Hash::make($this->password),
        ]);


        $user->syncRoles($this->roles);

        return to_route("users.index")->with("success", "User created successfully");
    }
}
