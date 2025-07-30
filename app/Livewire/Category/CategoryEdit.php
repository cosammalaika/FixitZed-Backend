<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Livewire\Component;

class CategoryEdit extends Component
{
    public $category;
    public $name;
    public $description;

    public function mount($id)
    {
        $this->category = Category::findOrFail($id);
        $this->name = $this->category->name;
        $this->description = $this->category->description;
    }

    public function render()
    {
        return view('livewire.category.category-edit');
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->category->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Category updated successfully.');
        return redirect()->route('category.index');
    }
}
