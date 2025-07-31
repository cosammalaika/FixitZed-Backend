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
        $role = Role::find($id);

        if ($role) {
            $name = $role->name;
            $role->delete();

            log_user_action('deleted role', "Role: {$name}");

            session()->flash('success', "Role deleted successfully.");
        } else {
            session()->flash('error', "Role not found.");
        }

        $roles = Role::with("permissions")->get();
        return view('livewire.role.role-index', compact("roles"));
    }

}
