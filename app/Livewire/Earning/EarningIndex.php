<?php

namespace App\Livewire\Earning;

use App\Models\Earning;
use Livewire\Component;

class EarningIndex extends Component
{
    protected $listeners = ['deleteEarning' => 'delete'];

    public function render()
    {
        return view('livewire.earning.earning-index', [
            'earnings' => Earning::latest()->with(['fixer.user'])->get()
        ]);
    }

    public function delete($id)
    {
        $earning = Earning::find($id);

        if ($earning) {
            $earning->delete();

            log_user_action('deleted earning', "Deleted earning ID: {$earning->id}");

            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Earning deleted successfully.',
            ]);
        } else {
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'error',
                'message' => 'Earning not found.',
            ]);
        }
    }
}
