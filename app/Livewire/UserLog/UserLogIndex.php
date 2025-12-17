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
        $perPage = (int) setting('admin.per_page', 20);
        $perPage = max(5, min($perPage, 200));
        $logs = UserLog::with('user')->latest()->paginate($perPage);
        return view('livewire.user-log.user-log-index', compact('logs'));
    }
}
