<?php

namespace App\Livewire\Service;

use App\Models\Service;
use Livewire\Component;

class ServiceIndex extends Component
{
    public function render()
    {
        $services = Service::get();
        return view('livewire.service.service-index', compact("services"));
    }
    public function delete($id)
    {
        $service = Service::find($id);

        if ($service) {
            $name = $service->name;
            $service->delete();

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
