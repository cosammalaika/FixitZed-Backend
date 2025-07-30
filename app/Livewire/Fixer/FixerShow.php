<?php

namespace App\Livewire\Fixer;

use App\Models\Fixer;
use Livewire\Component;

class FixerShow extends Component
{
    public $fixerId;
    public $fixer;

    public function mount($id)
    {
        $this->fixerId = $id;
        $this->fixer = Fixer::with('user')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.fixer.fixer-show');
    }
}
