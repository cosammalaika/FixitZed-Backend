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
    public $serviceId, $name, $category, $description, $status;

    protected $rules = [];

    public function mount($id)
    {
        $service = Service::findOrFail($id);

        $this->serviceId = $service->id;
        $this->name = $service->name;
        $this->category = $service->category;
        $this->description = $service->description;
        $this->status = $service->status;
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
        $uniqueName = Rule::unique('services', 'name');

        if (Schema::hasColumn('services', 'subcategory_id')) {
            $subcategoryId = $this->resolvedSubcategoryId();
            if ($subcategoryId !== null) {
                $uniqueName = $uniqueName->where(fn ($q) => $q->where('subcategory_id', $subcategoryId));
            }
        } elseif (Schema::hasColumn('services', 'category')) {
            $category = trim((string) $this->category);
            $uniqueName = $uniqueName->where(fn ($q) => $q->where('category', $category));
        }

        $uniqueName = $uniqueName->ignore($this->serviceId);

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $uniqueName,
            ],
            'category' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! Schema::hasColumn('services', 'subcategory_id')) {
                        return;
                    }

                    if ($this->resolvedSubcategoryId() === null) {
                        $fail('Please enter a valid subcategory name.');
                    }
                },
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function fillService(Service $service): void
    {
        $service->name = trim((string) $this->name);
        $service->description = $this->nullableTrimmedString($this->description);
        $service->category = trim((string) $this->category);

        if (Schema::hasColumn('services', 'subcategory_id')) {
            $subcategoryId = $this->resolvedSubcategoryId();
            if ($subcategoryId !== null) {
                $service->subcategory_id = $subcategoryId;
            }
        }

        $service->is_active = $this->status === 'active';
    }

    protected function resolvedSubcategoryId(): ?int
    {
        $name = trim((string) $this->category);
        if ($name === '') {
            return null;
        }

        if (! Schema::hasTable('subcategories')) {
            return null;
        }

        $subcategory = Subcategory::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        return $subcategory?->id;
    }

    protected function nullableTrimmedString(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text === '' ? null : $text;
    }
}
