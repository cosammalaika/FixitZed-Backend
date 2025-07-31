<?php

namespace App\Livewire\Role;

use Livewire\Component;
use Spatie\Permission\Models\Role;

use Spatie\Permission\Models\Permission;

class RoleCreate extends Component
{
    public $name, $permissions = [], $allPermissions = [];

    public function mount()
    {
        $this->allPermissions = Permission::all();
    }
    public function render()
    {
        return view('livewire.role.role-create');
    }
    public function submit()
    {
        $this->validate([
            "name" => "required|unique:roles,name",
            "permissions" => "required"
        ]);

        $role = Role::create([
            "name" => $this->name
        ]);
        $role->syncPermissions($this->permissions);

        log_user_action('created role', "Role: {$this->name}, Permissions: " . implode(', ', $this->permissions));

        return to_route("role.index")->with("success", "Role created successfully");
    }
}
