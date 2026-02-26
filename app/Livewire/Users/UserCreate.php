<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Rules\RealEmailAddress;
use App\Rules\RealHumanName;
use App\Support\ProvinceDistrict;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class UserCreate extends Component
{
    use WithFileUploads;
    public $first_name, $last_name, $email, $password, $confirm_password, $username, $contact_number, $status = 'Active';
    public $allRoles, $roles = [];
    public bool $canAssignRoles = false;
    public $province = '';
    public $district = '';
    public array $provinceOptions = [];
    public array $districtOptions = [];

    /** @var array<string, array<int, string>> */
    public array $provinceDistricts = [];

    // Uploads
    public $photo; // profile image
    public $nrc_front;
    public $nrc_back;
    public $documents = [];


    public function mount()
    {
        $this->canAssignRoles = auth()->user()?->can('assign.permissions') ?? false;
        if ($this->canAssignRoles) {
            $this->allRoles = Role::orderBy('name')->get();
        } else {
            $this->roles = ['Customer'];
            $this->allRoles = Role::where('name', 'Customer')->get();
        }
        $this->loadProvinceData();
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
        if (empty($this->provinceDistricts)) {
            $this->loadProvinceData();
        }
        $this->districtOptions = $this->provinceDistricts[$value] ?? [];
        if (! in_array($this->district, $this->districtOptions, true)) {
            $this->district = '';
        }
    }

    public function render()
    {
        return view('livewire.users.user-create', [
            'provinceMap' => $this->provinceDistricts,
        ]);
    }

    public function submit()
    {
        $rolesToAssign = $this->canAssignRoles ? array_filter((array) $this->roles) : ['Customer'];
        if (empty($rolesToAssign)) {
            $rolesToAssign = ['Customer'];
        }

        $this->first_name = trim((string) $this->first_name);
        $this->last_name = trim((string) $this->last_name);
        $this->email = strtolower(trim((string) $this->email));
        $this->username = trim((string) $this->username);
        $this->contact_number = trim((string) $this->contact_number);

        $this->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'username' => 'required|string|max:255|unique:users,username',
            'email' => RealEmailAddress::rules(),
            'contact_number' => 'required|string|max:20',
            'status' => 'required|in:Active,Inactive',
            'province' => 'required|string',
            'district' => 'required|string',
            'roles' => 'nullable',
            'password' => 'required|same:confirm_password|min:6',
            // files (increase limits to handle phone camera sizes)
            'photo' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'nrc_front' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'nrc_back' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
            'documents.*' => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png,webp'
        ]);

        if (! $this->validateRealHumanName()) {
            return;
        }

        $address = trim($this->province . ', ' . $this->district);

        $user = User::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'status' => $this->status,
            'province' => $this->province,
            'district' => $this->district,
            'address' => $address,
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

        $user->syncRoles($rolesToAssign);
        log_user_action('created user', "Created user ID: {$user->id}, Email: {$user->email}");


        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'User created successfully.',
            'redirect' => route('users.index'),
        ]);
    }
    protected function validateRealHumanName(): bool
    {
        $validator = Validator::make(
            ['name' => trim($this->first_name . ' ' . $this->last_name)],
            ['name' => RealHumanName::rules()],
            ['name.required' => 'First and last name are required.']
        );

        if ($validator->fails()) {
            $this->addError('first_name', $validator->errors()->first('name'));
            return false;
        }

        return true;
    }

}
