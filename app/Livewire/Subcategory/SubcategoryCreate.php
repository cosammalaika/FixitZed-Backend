<?php

namespace App\Livewire\Subcategory;

use App\Models\Category;
use App\Models\Subcategory;
use Livewire\Component;

class SubcategoryCreate extends Component
{
    public $category_id;
    public $name;
    public $description;
    public $categories;

    public function mount()
    {
        $this->categories = Category::all();
    }

    public function render()
    {
        return view('livewire.subcategory.subcategory-create');
    }

    public function submit()
    {
        $this->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
        ]);

        log_user_action('created subcategory', "Subcategory ID: {$subcategory->id}, Name: {$subcategory->name}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Subcategory created successfully.',
            'redirect' => route('subcategory.index'),
        ]);
    }

}
