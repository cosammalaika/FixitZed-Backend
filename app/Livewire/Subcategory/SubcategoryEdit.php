<?php

namespace App\Livewire\Subcategory;

use App\Models\Category;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Illuminate\Validation\Rule;
use Livewire\Component;

class SubcategoryEdit extends Component
{
    public $subcategory;
    public $category_id;
    public $name;
    public $description;
    public $categories;

    public function mount($id)
    {
        $this->subcategory = Subcategory::findOrFail($id);
        $this->category_id = $this->subcategory->category_id;
        $this->name = $this->subcategory->name;
        $this->description = $this->subcategory->description;
        $this->categories = Category::all();
    }

    public function render()
    {
        return view('livewire.subcategory.subcategory-edit');
    }

    public function update()
    {
        $this->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subcategories', 'name')
                    ->where(fn ($q) => $q->where('category_id', $this->category_id))
                    ->ignore($this->subcategory->id),
            ],
            'description' => 'nullable|string',
        ]);

        $this->subcategory->update([
            'category_id' => $this->category_id,
            'name' => trim((string) $this->name),
            'description' => trim((string) $this->description),
        ]);

        ApiCache::flush(['catalog', 'categories', 'subcategories', 'services']);
        log_user_action('updated subcategory', "Subcategory ID: {$this->subcategory->id}, Name: {$this->subcategory->name}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Subcategory updated successfully.',
            'redirect' => route('subcategory.index'),
        ]);
    }

}
