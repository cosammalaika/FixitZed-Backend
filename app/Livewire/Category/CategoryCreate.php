<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Livewire\Component;

class CategoryCreate extends Component
{
    public $name;
    public $description;

    public function render()
    {
        return view('livewire.category.category-create');
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        log_user_action('created category', "Created category ID: {$category->id}, Name: {$category->name}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Category created successfully.',
            'redirect' => route('category.index'),
        ]);
    }
}
