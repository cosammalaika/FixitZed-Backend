<?php

namespace App\Livewire\Service;

use App\Models\Service;
use App\Models\Subcategory;
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
        $uniqueName = Rule::unique('services', 'name');

        if (Schema::hasColumn('services', 'subcategory_id')) {
            $subcategoryId = $this->resolvedSubcategoryId();
            if ($subcategoryId !== null) {
                $uniqueName = $uniqueName->where(fn ($q) => $q->where('subcategory_id', $subcategoryId));
            }
        }

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

    public function render()
    {
        return view('livewire.service.service-create');
    }

    protected function fillService(Service $service): void
    {
        $service->name = trim((string) $this->name);
        $service->description = $this->nullableTrimmedString($this->description);

        // Keep legacy UI input (`category`) while persisting canonical schema fields.
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
