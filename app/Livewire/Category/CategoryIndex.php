<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Livewire\Component;

class CategoryIndex extends Component
{
    protected $listeners = ['deleteCategory' => 'delete'];

    public function render()
    {
        $categories = Category::get();
        return view('livewire.category.category-index', compact("categories"));
    }
    public function delete($id)
    {
        $category = Category::find($id);

        if ($category) {
            $category->delete();

            log_user_action('deleted category', "Deleted category ID: {$id}, Name: {$category->name}");

            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Category deleted successfully.',
            ]);
        } else {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Category not found.',
            ]);
        }
    }

}
