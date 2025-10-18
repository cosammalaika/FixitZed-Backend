<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Support\ProvinceDistrict;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class UserEdit extends Component
{
    use WithFileUploads;
    public $user;
    public $first_name, $last_name, $username, $contact_number, $status, $email;
    public $province = '';
    public $district = '';
    public array $provinceOptions = [];
    public array $districtOptions = [];
    protected array $provinceDistricts = [];
    public $allRoles, $roles = [];
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
        $this->status = $this->user->status;
        $this->email = $this->user->email;
        $this->allRoles = Role::all();
        $this->roles = $this->user->roles()->pluck('name')->all();

        $this->loadProvinceData();
        $this->province = $this->user->province ?? '';
        $this->district = $this->user->district ?? '';
        if ($this->province) {
            $this->updatedProvince($this->province);
            if (! in_array($this->district, $this->districtOptions, true)) {
                $this->district = '';
            }
        }
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
            'status' => 'required|in:Active,Inactive',
            'province' => 'required|string',
            'district' => 'required|string',
            'photo' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'nrc_front' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'nrc_back' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'documents.*' => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png,webp',
        ]);

        $address = trim($this->province . ', ' . $this->district);

        $this->user->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'contact_number' => $this->contact_number,
            'status' => $this->status,
            'email' => $this->email,
            'province' => $this->province,
            'district' => $this->district,
            'address' => $address,
        ]);
        // if ($this->password) {
        //     $this->user->password = Hash::make($this->password);
        // }

        $this->user->save();

        // Handle optional file updates
        $baseDir = 'users/' . $this->user->id;
        $updates = [];
        if ($this->photo) {
            if ($this->user->profile_photo_path) {
                Storage::disk('public')->delete($this->user->profile_photo_path);
            }
            $path = $this->photo->store($baseDir, 'public');
            $updates['profile_photo_path'] = $path;
            $this->user->profile_photo_path = $path;
        }
        if ($this->nrc_front) {
            if ($this->user->nrc_front_path) {
                Storage::disk('public')->delete($this->user->nrc_front_path);
            }
            $path = $this->nrc_front->store($baseDir, 'public');
            $updates['nrc_front_path'] = $path;
            $this->user->nrc_front_path = $path;
        }
        if ($this->nrc_back) {
            if ($this->user->nrc_back_path) {
                Storage::disk('public')->delete($this->user->nrc_back_path);
            }
            $path = $this->nrc_back->store($baseDir, 'public');
            $updates['nrc_back_path'] = $path;
            $this->user->nrc_back_path = $path;
        }
        if (is_array($this->documents) && count($this->documents)) {
            $existing = is_array($this->user->documents) ? $this->user->documents : [];
            foreach ($this->documents as $doc) {
                if ($doc) {
                    $existing[] = $doc->store($baseDir . '/documents', 'public');
                }
            }
            $updates['documents'] = $existing;
            $this->user->documents = $existing;
        }
        if (!empty($updates)) {
            $this->user->update($updates);
        }

        $this->reset(['photo', 'nrc_front', 'nrc_back', 'documents']);

        $roles = array_filter((array) $this->roles);
        if (empty($roles)) {
            $roles = ['Customer'];
        }
        $this->user->syncRoles($roles);
        log_user_action('updated user', "User ID: {$this->user->id}, Name: {$this->user->first_name} {$this->user->last_name}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'User updated successfully.',
            'redirect' => route('users.index'),
        ]);
    }

    protected function loadProvinceData(): void
    {
        $map = ProvinceDistrict::map();
        $this->provinceDistricts = $map;
        $this->provinceOptions = array_keys($map);
        $this->districtOptions = [];
    }

    public function updatedProvince($value): void
    {
        $value = (string) $value;
        $this->province = $value;
        $this->districtOptions = $this->provinceDistricts[$value] ?? [];
        if (! in_array($this->district, $this->districtOptions, true)) {
            $this->district = '';
        }
    }

*** End Patch
*** End Patch
{"error":"Patch must end with *** End Patch"}}:
}
