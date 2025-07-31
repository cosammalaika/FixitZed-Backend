<?php

namespace App\Livewire\Earning;

use App\Models\Earning;
use App\Models\Fixer;
use App\Models\ServiceRequest;
use Livewire\Component;

class EarningEdit extends Component
{
    public $earning;
    public $fixer_id, $service_count, $amount;

    public function mount(Earning $earning)
    {
        $this->earning = $earning;
        $this->fixer_id = $earning->fixer_id;
        $this->service_count = $earning->service_count;
        $this->amount = $earning->amount;
    }

    public function update()
    {
        $this->validate([
            'fixer_id' => 'required|exists:fixers,id',
            'service_count' => 'required|exists:service_requests,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $this->earning->update([
            'fixer_id' => $this->fixer_id,
            'service_count' => $this->service_count,
            'amount' => $this->amount,
        ]);

        log_user_action('updated earning', "Updated earning ID: {$this->earning->id}");

        session()->flash('success', 'Earning updated successfully!');
        return redirect()->route('earnings.index');
    }

    public function render()
    {
        return view('livewire.earning.earning-edit', [
            'fixers' => Fixer::all(),
            'requests' => ServiceRequest::all()
        ]);
    }
}
