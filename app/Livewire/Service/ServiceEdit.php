<?php

namespace App\Livewire\Service;

use Livewire\Component;
use App\Models\Service;
use App\Support\ApiCache;
use Illuminate\Validation\Rule;

class ServiceEdit extends Component
{
    public $serviceId, $name, $category, $description, $status;

    protected $rules = [];

    public function mount($id)
    {

        $service = Service::find($id);
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

        $service->update([
            'name' => trim((string) $this->name),
            'category' => trim((string) $this->category),
            'description' => trim((string) $this->description),
            'status' => $this->status,
        ]);

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
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'name')
                    ->where(fn ($q) => $q->where('category', $this->category))
                    ->ignore($this->serviceId),
            ],
            'category' => ['required', 'string', 'max:255'],
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }



}
