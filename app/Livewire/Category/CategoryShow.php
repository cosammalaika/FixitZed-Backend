<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Livewire\Component;

class CategoryShow extends Component
{
    public $category;

    public function mount($id)
    {
        $this->category = Category::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.category.category-show');
    }
}
