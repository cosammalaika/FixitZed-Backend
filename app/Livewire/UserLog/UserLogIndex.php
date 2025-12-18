<?php
// app/Livewire/UserLog/UserLogIndex.php
namespace App\Livewire\UserLog;

use App\Models\UserLog;
use Livewire\Component;
use Livewire\WithPagination;

class UserLogIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $logs = UserLog::with('user')->latest()->paginate(25);
        return view('livewire.user-log.user-log-index', compact('logs'));
    }
}
