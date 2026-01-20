<?php

namespace App\Livewire\Service;

use Livewire\Component;
use App\Models\Service;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Illuminate\Validation\Rule;
class ServiceCreate extends Component
{
    public $name, $description, $price, $is_active = true, $subcategory_id, $duration_minutes;
    public $subcategories;
    protected $rules = [];

    public function mount()
    {
        $this->subcategories = Subcategory::orderBy('name')->get();
    }

    public function submit()
    {
        $this->validate($this->rules());

        $service = Service::create([
            'name' => trim((string) $this->name),
            'description' => trim((string) $this->description),
            'price' => ($this->price === '' || $this->price === null) ? 0 : $this->price,
            'duration_minutes' => ($this->duration_minutes === '' || $this->duration_minutes === null) ? 60 : $this->duration_minutes,
            'subcategory_id' => $this->subcategory_id,
            'is_active' => $this->is_active == "1" ? true : false,
        ]);

        ApiCache::flush(['catalog', 'categories', 'subcategories', 'services']);
        log_user_action('created service', "Service: {$service->name}, ID: {$service->id}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Service created successfully.',
            'redirect' => route('services.index'),
        ]);
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'name')->where(fn ($q) => $q->where('subcategory_id', $this->subcategory_id)),
            ],
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|numeric|min:0',
            'subcategory_id' => 'required|exists:subcategories,id',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        return view('livewire.service.service-create', [
            'subcategories' => $this->subcategories,
        ]);
    }
}
