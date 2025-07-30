<?php

namespace App\Livewire\Subcategory;

use App\Models\Subcategory;
use Livewire\Component;

class SubcategoryIndex extends Component
{
    public function render()
    {
        $subcategories = Subcategory::get();
         return view('livewire.subcategory.subcategory-index', [
            'subcategories' => Subcategory::with('category')->latest()->get()
        ]);
    }
    public function delete($id)
    {
        $subcategories = Subcategory::find($id);

        $subcategories->delete();
        session()->flash('success', "Subcategory deleted successfully.");
        return view('livewire.subcategory.subcategory-index', compact("subcategories"));

    }
}
