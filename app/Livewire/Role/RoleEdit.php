<?php

namespace App\Livewire\Role;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleEdit extends Component
{
    public $name, $role, $permissions = [], $allPermissions = [];

    public function mount($id)
    {
        $this->role = Role::find($id);
        $this->allPermissions = Permission::all();
        $this->name = $this->role->name;
        $this->permissions= $this->role->permissions()->pluck("name");
    }
     public function submit()
    {
        $this ->validate([
            "name"=>"required|unique:roles,name,".$this->role->id,
            "permissions"=>"required"
        ]);

       $this->role->name = $this->name;
       $this->role->save(); 
       $this->role->syncPermissions($this -> permissions);
        return to_route("role.index")->with("success","Role Edited successfully");
    }
    public function render()
    {
        return view('livewire.role.role-edit');
    }
}
