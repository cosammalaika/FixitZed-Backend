<?php

namespace App\Livewire\Subcategory;

use App\Models\Subcategory;
use Livewire\Component;

class SubcategoryIndex extends Component
{
    protected $listeners = ['deleteSubcategory' => 'delete'];

    public function render()
    {
        $subcategories = Subcategory::get();
        return view('livewire.subcategory.subcategory-index', [
            'subcategories' => Subcategory::with('category')->latest()->get()
        ]);
    }
    public function delete($id)
    {
        $subcategory = Subcategory::find($id);

        if ($subcategory) {
            $subcategory->delete();
            log_user_action('deleted subcategory', "Subcategory ID: {$id}, Name: {$subcategory->name}");
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Subcategory deleted successfully.',
            ]);
        } else {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Subcategory not found.',
            ]);
        }
    }

}
