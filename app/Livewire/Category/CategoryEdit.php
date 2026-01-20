<?php

namespace App\Livewire\Category;

use App\Models\Category;
use App\Support\ApiCache;
use Illuminate\Validation\Rule;
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->category->id),
            ],
            'description' => 'nullable|string',
        ]);

        $this->category->update([
            'name' => trim((string) $this->name),
            'description' => trim((string) $this->description),
        ]);

        ApiCache::flush(['catalog', 'categories', 'subcategories', 'services']);
        log_user_action('updated category', "Updated category ID: {$this->category->id}, New Name: {$this->name}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Category updated successfully.',
            'redirect' => route('category.index'),
        ]);
    }
}
