<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Livewire\Component;

class CategoryIndex extends Component
{
    public function render()
    {
        $categories = Category::get();
        return view('livewire.category.category-index', compact("category"));
    }
    public function delete($id)
    {
        $categories = Category::find($id);

        $categories->delete();
        session()->flash('success', "Category deleted successfully.");
        return view('livewire.category.category-index', compact("category"));

    }

}
