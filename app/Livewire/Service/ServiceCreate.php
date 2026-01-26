<?php

namespace App\Livewire\Service;

use Livewire\Component;
use App\Models\Service;
use App\Support\ApiCache;
use Illuminate\Validation\Rule;
class ServiceCreate extends Component
{
    public $name, $category, $description, $status = 'active';
    protected $rules = [];

    public function submit()
    {
        $this->validate($this->rules());

        $service = Service::create([
            'name' => trim((string) $this->name),
            'category' => trim((string) $this->category),
            'description' => trim((string) $this->description),
            'status' => $this->status,
        ]);

        ApiCache::flush(['catalog', 'services']);
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
                Rule::unique('services', 'name')->where(fn ($q) => $q->where('category', $this->category)),
            ],
            'category' => ['required', 'string', 'max:255'],
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function render()
    {
        return view('livewire.service.service-create');
    }
}
