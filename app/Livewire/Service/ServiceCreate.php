<?php

namespace App\Livewire\Service;

use App\Models\Service;
use App\Support\ApiCache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ServiceCreate extends Component
{
    public $name, $category, $description, $status = 'active';

    protected $rules = [];

    public function submit()
    {
        $this->validate($this->rules());

        $service = new Service();
        $this->fillService($service);
        $service->save();

        ApiCache::flush(['catalog', 'services', 'categories', 'subcategories']);
        log_user_action('created service', "Service: {$service->name}, ID: {$service->id}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Service created successfully.',
            'redirect' => route('services.index'),
        ]);
    }

    protected function rules(): array
    {
        $uniqueName = Rule::unique('services', 'name');

        $category = trim((string) $this->category);
        if (Schema::hasColumn('services', 'category') && $category !== '') {
            $uniqueName = $uniqueName->where(fn ($q) => $q->where('category', $category));
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $uniqueName,
            ],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function render()
    {
        return view('livewire.service.service-create');
    }

    protected function fillService(Service $service): void
    {
        $service->name = trim((string) $this->name);
        $service->category = trim((string) $this->category);
        $service->description = $this->nullableTrimmedString($this->description);
        $service->is_active = $this->status === 'active';
    }

    protected function nullableTrimmedString(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text === '' ? null : $text;
    }
}
