<?php

namespace App\Livewire\Subcategory;

use App\Models\Category;
use App\Models\Subcategory;
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->subcategory->update([
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
        ]);

        log_user_action('updated subcategory', "Subcategory ID: {$this->subcategory->id}, Name: {$this->subcategory->name}");

        session()->flash('success', 'Subcategory updated successfully.');
        return redirect()->route('subcategory.index');
    }

}
