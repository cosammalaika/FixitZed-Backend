<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserShow extends Component
{
    public $user,$allRoles;
      public function mount($id)
    {
        $this->user = User::find($id);
         $this->allRoles = Role::all();
        $this->roles = $this->user->roles()->pluck("name"); // safer to use findOrFail
        
    }
    public function render()
    {
        return view('livewire.users.user-show');
    }
}
