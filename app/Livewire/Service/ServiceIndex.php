<?php

namespace App\Livewire\Service;

use App\Models\Service;
use App\Support\ApiCache;
use Livewire\Component;

class ServiceIndex extends Component
{
    protected $listeners = ['deleteService' => 'delete'];

    public function render()
    {
        $services = Service::query()
            ->with(['subcategory.category'])
            ->orderBy('name')
            ->get();

        return view('livewire.service.service-index', compact('services'));
    }

    public function delete($id)
    {
        $service = Service::find($id);

        if ($service) {
            $name = $service->name;
            $service->delete();

            ApiCache::flush(['catalog', 'services']);
            log_user_action('deleted service', "Service: {$name}, ID: {$id}");

            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Service deleted successfully.',
            ]);
        } else {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Service not found.',
            ]);
        }
    }
}
