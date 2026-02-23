<?php

namespace App\Livewire\Service;

use App\Models\Service;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ServiceCreate extends Component
{
    public ?int $subcategory_id = null;

    public $name, $category, $description, $status = 'active';

    protected $rules = [];

    public function submit()
    {
        $this->validate($this->rules());

        $service = new Service();
        $this->fillService($service);

        try {
            $service->save();
        } catch (QueryException $e) {
            report($e);

            if ($this->looksLikeMissingSubcategoryConstraint($e)) {
                $this->addError('subcategory_id', 'Please select a valid subcategory before saving.');
            }

            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Unable to create service right now. Please review the form and try again.',
            ]);

            return;
        }

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

        if (Schema::hasColumn('services', 'subcategory_id') && $this->subcategory_id) {
            $uniqueName = $uniqueName->where(fn ($q) => $q->where('subcategory_id', (int) $this->subcategory_id));
        } else {
            $category = trim((string) $this->category);
            if (Schema::hasColumn('services', 'category') && $category !== '') {
                $uniqueName = $uniqueName->where(fn ($q) => $q->where('category', $category));
            }
        }

        $subcategoryRules = ['nullable'];
        if (Schema::hasColumn('services', 'subcategory_id') && Schema::hasTable('subcategories')) {
            $subcategoryRules = ['required', 'integer', Rule::exists('subcategories', 'id')];
        }

        return [
            'subcategory_id' => $subcategoryRules,
            'name' => [
                'required',
                'string',
                'max:255',
                $uniqueName,
            ],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function updatedSubcategoryId($value): void
    {
        $id = is_numeric($value) ? (int) $value : null;
        $this->subcategory_id = $id;

        if (! $id || ! Schema::hasTable('subcategories')) {
            return;
        }

        $subcategory = Subcategory::query()
            ->select(['id', 'name'])
            ->find($id);

        if ($subcategory) {
            // Keep the legacy display/category string aligned with the selected subcategory label.
            $this->category = $subcategory->name;
        }
    }

    public function render()
    {
        $subcategories = collect();
        if (Schema::hasTable('subcategories')) {
            $subcategories = Subcategory::query()
                ->with('category:id,name')
                ->orderBy('name')
                ->get(['id', 'category_id', 'name']);
        }

        return view('livewire.service.service-create', [
            'subcategories' => $subcategories,
        ]);
    }

    protected function fillService(Service $service): void
    {
        $service->name = trim((string) $this->name);

        $selectedSubcategory = null;
        if (Schema::hasColumn('services', 'subcategory_id') && Schema::hasTable('subcategories') && $this->subcategory_id) {
            $selectedSubcategory = Subcategory::query()
                ->select(['id', 'name'])
                ->find((int) $this->subcategory_id);

            $service->subcategory_id = (int) $this->subcategory_id;
        }

        if (Schema::hasColumn('services', 'category')) {
            $service->category = $selectedSubcategory?->name ?? trim((string) $this->category);
        }

        $service->description = $this->nullableTrimmedString($this->description);
        $service->is_active = $this->status === 'active';
    }

    protected function nullableTrimmedString(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text === '' ? null : $text;
    }

    protected function looksLikeMissingSubcategoryConstraint(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'subcategory_id')
            || str_contains($message, '1364')
            || str_contains($message, 'cannot be null');
    }
}
