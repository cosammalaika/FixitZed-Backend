<?php

namespace App\Livewire\Service;

use App\Models\Service;
use Livewire\Component;

class ServiceIndex extends Component
{
    public function render()
    {
        $services = Service::get();
        return view('livewire.service.service-index',compact("services"));
    }
    public function delete($id)
    {
        $services = Service::find($id);

            $services->delete();
            session()->flash('success', "Service deleted successfully.");
            return view('livewire.service.service-index',compact("services"));
        
    }
}
