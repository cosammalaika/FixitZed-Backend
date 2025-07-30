<?php

namespace App\Livewire\Subcategory;

use App\Models\Subcategory;
use Livewire\Component;

class SubcategoryShow extends Component
{
    public $subcategory;

    public function mount($id)
    {
        $this->subcategory = Subcategory::with('category')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.subcategory.subcategory-show');
    }
}
