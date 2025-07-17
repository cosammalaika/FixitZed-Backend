<?php

namespace App\Livewire\Service;

use Livewire\Component;
use App\Models\Service;

class ServiceShow extends Component
{
    public $service;

    public function mount($id)
    {
        $this->service = Service::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.service.service-show');
    }
}
