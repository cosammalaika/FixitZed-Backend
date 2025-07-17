<?php

namespace App\Livewire\Role;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class RoleIndex extends Component
{
    public function render()
    {
        $roles = Role::with("permissions")->get();
        return view('livewire.role.role-index', compact("roles"));
    }

    
     public function delete($id)
    {
        $roles = Role::find($id);

            $roles->delete();
            session()->flash('success', "Service deleted successfully.");
        return view('livewire.role.role-index', compact("roles"));
        
    }

}
