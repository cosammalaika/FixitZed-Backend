<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\LocationOption;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class UserCreate extends Component
{
    use WithFileUploads;
    public $first_name, $last_name, $email, $password, $confirm_password, $username, $contact_number, $user_type = 'user', $status = 'Active', $address;
    public $allRoles, $roles = [];
    public $location_option_id;
    public $locationOptions = [];

    // Uploads
    public $photo; // profile image
    public $nrc_front;
    public $nrc_back;
    public $documents = [];


    public function mount()
    {
        $this->allRoles = Role::all();
        if (Schema::hasTable('location_options')) {
            $this->locationOptions = LocationOption::where('is_active', true)->orderBy('name')->get();
        } else {
            $this->locationOptions = collect();
        }
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
            'location_option_id' => 'required|exists:location_options,id',
            'roles' => 'nullable',
            'password' => 'required|same:confirm_password|min:6',
            // files (increase limits to handle phone camera sizes)
            'photo' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'nrc_front' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'nrc_back' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'documents.*' => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png,webp'
        ]);

        $selectedLocation = LocationOption::find($this->location_option_id);

        $user = User::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'user_type' => $this->user_type,
            'status' => $this->status,
            'address' => $selectedLocation?->name,
            'password' => Hash::make($this->password),
        ]);

        // Store files to public disk under users/{id}/
        $baseDir = 'users/' . $user->id;
        $paths = [
            'profile_photo_path' => null,
            'nrc_front_path' => null,
            'nrc_back_path' => null,
            'documents' => [],
        ];

        if ($this->photo) {
            $paths['profile_photo_path'] = $this->photo->store($baseDir, 'public');
        }
        if ($this->nrc_front) {
            $paths['nrc_front_path'] = $this->nrc_front->store($baseDir, 'public');
        }
        if ($this->nrc_back) {
            $paths['nrc_back_path'] = $this->nrc_back->store($baseDir, 'public');
        }
        if (is_array($this->documents)) {
            foreach ($this->documents as $doc) {
                if ($doc) {
                    $paths['documents'][] = $doc->store($baseDir . '/documents', 'public');
                }
            }
        }

        $user->update($paths);


        $user->syncRoles($this->roles);
        log_user_action('created user', "Created user ID: {$user->id}, Email: {$user->email}");


        return to_route("users.index")->with("success", "User created successfully");
    }
}
