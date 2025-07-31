<?php

namespace App\Livewire\Earning;

use App\Models\Earning;
use Livewire\Component;

class EarningShow extends Component
{
    public $earning;
    

    public function mount(Earning $earning)
    {
$this->earning = $earning->load('fixer.user');    }

    public function render()
    {
        return view('livewire.earning.earning-show');
    }
}
