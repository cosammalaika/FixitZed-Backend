<?php

namespace App\Livewire;

use App\Models\Fixer;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalUsers, $totalFixers, $activeRequests, $serviceCompleted, $newUsersThisWeek, $newFixerThisWeek, $newActiveRequests, $newServiceCompleted;

    public function mount()
    {
        $this->totalUsers = User::where('status', 'Active')->count();
        $this->totalFixers = Fixer::where('status', 'Active')->count();
        $this->activeRequests = ServiceRequest::where('status', 'pending')->count();
        $this->serviceCompleted = ServiceRequest::where('status', 'completed')->count();

        $this->newUsersThisWeek = User::where('created_at', '>=', Carbon::now()->subWeek())->count();
        $this->newFixerThisWeek = Fixer::where('created_at', '>=', Carbon::now()->subWeek())->count();
        $this->newActiveRequests = ServiceRequest::where('status', 'pending')
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->count();

        $this->newServiceCompleted = ServiceRequest::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->count();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
