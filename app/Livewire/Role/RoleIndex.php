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

            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Role deleted successfully.',
            ]);
        } else {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Role not found.',
            ]);
        }
    }

}
