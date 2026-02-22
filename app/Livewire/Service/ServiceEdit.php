<?php

namespace App\Livewire\Service;

use App\Models\Service;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ServiceEdit extends Component
{
    public $serviceId, $name, $subcategory_id, $description, $status;

    public array $subcategoryOptions = [];

    protected $rules = [];

    public function mount($id)
    {
        $service = Service::with('subcategory.category')->findOrFail($id);

        $this->serviceId = $service->id;
        $this->name = $service->name;
        $this->subcategory_id = $service->subcategory_id;
        $this->description = $service->description;
        $this->status = $service->status;

        $this->subcategoryOptions = $this->loadSubcategoryOptions();
    }

    public function render()
    {
        return view('livewire.service.service-edit');
    }

    public function update()
    {
        $this->validate($this->rules());

        $service = Service::findOrFail($this->serviceId);
        $oldName = $service->name;

        $this->fillService($service);
        $service->save();

        ApiCache::flush(['catalog', 'services']);
        log_user_action('updated service', "From '{$oldName}' to '{$this->name}', ID: {$service->id}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Service updated successfully.',
            'redirect' => route('services.index'),
        ]);
    }

    protected function rules(): array
    {
        $uniqueName = Rule::unique('services', 'name')
            ->ignore($this->serviceId);

        if (Schema::hasColumn('services', 'subcategory_id') && $this->subcategory_id !== null && $this->subcategory_id !== '') {
            $uniqueName = $uniqueName->where(fn ($q) => $q->where('subcategory_id', (int) $this->subcategory_id));
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $uniqueName,
            ],
            'subcategory_id' => Schema::hasColumn('services', 'subcategory_id') && Schema::hasTable('subcategories')
                ? ['required', 'integer', Rule::exists('subcategories', 'id')]
                : ['nullable'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function fillService(Service $service): void
    {
        $payload = [
            'name' => trim((string) $this->name),
            'is_active' => $this->status === 'active',
        ];

        if (Schema::hasColumn('services', 'description')) {
            $payload['description'] = $this->nullableTrimmedString($this->description);
        }

        if (Schema::hasColumn('services', 'subcategory_id') && $this->subcategory_id !== null && $this->subcategory_id !== '') {
            $payload['subcategory_id'] = (int) $this->subcategory_id;
        }

        $service->fill($payload);
    }

    protected function loadSubcategoryOptions(): array
    {
        if (! Schema::hasTable('subcategories')) {
            return [];
        }

        return Subcategory::query()
            ->with('category:id,name')
            ->orderBy('name')
            ->get()
            ->map(function (Subcategory $subcategory) {
                $categoryName = $subcategory->category?->name;
                $prefix = is_string($categoryName) && $categoryName !== '' ? $categoryName . ' / ' : '';

                return [
                    'id' => (int) $subcategory->id,
                    'label' => $prefix . $subcategory->name,
                ];
            })
            ->values()
            ->all();
    }

    protected function nullableTrimmedString(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text === '' ? null : $text;
    }
}
