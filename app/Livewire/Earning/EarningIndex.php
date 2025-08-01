<?php

namespace App\Livewire\Earning;

use App\Models\Earning;
use Livewire\Component;

class EarningIndex extends Component
{
    public function render()
    {
        return view('livewire.earning.earning-index', [
            'earnings' => Earning::latest()->with(['fixer.user', 'serviceRequest'])->get()
        ]);
    }

    public function delete($id)
    {
        $earning = Earning::find($id);

        if ($earning) {
            $earning->delete();

            log_user_action('deleted earning', "Deleted earning ID: {$earning->id}");

            session()->flash('success', "Earning deleted successfully.");
        } else {
            session()->flash('success', "Earning not found.");
        }

        return redirect()->route('earning.index');
    }
}
