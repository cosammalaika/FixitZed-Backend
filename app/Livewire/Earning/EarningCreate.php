<?php

namespace App\Livewire\Earning;

use App\Models\Earning;
use App\Models\Fixer;
use App\Models\ServiceRequest;
use Livewire\Component;

class EarningCreate extends Component
{
    public $fixer_id, $service_count, $amount;

    public function save()
    {
        $this->validate([
            'fixer_id' => 'required|exists:fixers,id',
            'service_count' => 'required|exists:service_requests,id',
            'amount' => 'required|numeric|min:0',
        ]);

        Earning::create([
            'fixer_id' => $this->fixer_id,
            'service_count' => $this->service_count,
            'amount' => $this->amount,
        ]);

        session()->flash('success', 'Earning created successfully!');
        return redirect()->route('earnings.index');
    }

    public function render()
    {
        return view('livewire.earning.earning-create', [
            'fixers' => Fixer::all(),
            'requests' => ServiceRequest::all()
        ]);
    }
}
