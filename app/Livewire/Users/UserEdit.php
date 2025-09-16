<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class UserEdit extends Component
{
    use WithFileUploads;
    public $user;
    public $first_name, $last_name, $username, $contact_number, $user_type, $status, $email, $address, $allRoles, $roles = [];
    // Uploads (optional updates)
    public $photo; // profile image
    public $nrc_front;
    public $nrc_back;
    public $documents = [];

    public function mount($id)
    {
        $this->user = User::find($id); // safer to use findOrFail
        $this->first_name = $this->user->first_name;
        $this->last_name = $this->user->last_name;
        $this->username = $this->user->username;
        $this->contact_number = $this->user->contact_number;
        $this->user_type = $this->user->user_type;
        $this->status = $this->user->status;
        $this->email = $this->user->email;
        $this->address = $this->user->address;
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $this->user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $this->user->id,
            'contact_number' => 'required|string|max:20',
            'user_type' => 'required|in:Customer, Fixer, Admin, Support',
            'status' => 'required|in:Active,Inactive',
            'address' => 'nullable|string|max:1000',
            // files (increase limits to handle phone camera sizes)
            'photo' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'nrc_front' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'nrc_back' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'documents.*' => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png,webp',
            // 'password' => 'nullable|same:confirm_password|min:6',
        ]);

        $this->user->first_name = $this->first_name;
        $this->user->last_name = $this->last_name;
        $this->user->username = $this->username;
        $this->user->contact_number = $this->contact_number;
        $this->user->user_type = $this->user_type;
        $this->user->status = $this->status;
        $this->user->email = $this->email;
        $this->user->address = $this->address;

        // if ($this->password) {
        //     $this->user->password = Hash::make($this->password);
        // }

        $this->user->save();

        // Handle optional file updates
        $baseDir = 'users/' . $this->user->id;
        $updates = [];
        if ($this->photo) {
            $updates['profile_photo_path'] = $this->photo->store($baseDir, 'public');
        }
        if ($this->nrc_front) {
            $updates['nrc_front_path'] = $this->nrc_front->store($baseDir, 'public');
        }
        if ($this->nrc_back) {
            $updates['nrc_back_path'] = $this->nrc_back->store($baseDir, 'public');
        }
        if (is_array($this->documents) && count($this->documents)) {
            $existing = is_array($this->user->documents) ? $this->user->documents : [];
            foreach ($this->documents as $doc) {
                if ($doc) {
                    $existing[] = $doc->store($baseDir . '/documents', 'public');
                }
            }
            $updates['documents'] = $existing;
        }
        if (!empty($updates)) {
            $this->user->update($updates);
        }

        $this->user->syncRoles($this->roles);
        log_user_action('updated user', "User ID: {$this->user->id}, Name: {$this->user->first_name} {$this->user->last_name}");


        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }
}
