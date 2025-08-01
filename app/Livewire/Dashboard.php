<?php

namespace App\Livewire;

use App\Models\Fixer;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalUsers, $totalFixers, $activeRequests, $serviceCompleted, $newUsersThisWeek, $newFixerThisWeek, $newActiveRequests, $newServiceCompleted, $recentCustomers, $recentRequests;
    public $topRatedFixers;

    public function mount()
    {
        $this->totalUsers = User::where('status', 'Active')->count();
        $this->totalFixers = Fixer::where('status', 'approved')->count();
        $this->activeRequests = ServiceRequest::where('status', 'accepted')->count();
        $this->serviceCompleted = ServiceRequest::where('status', 'completed')->count();

        $this->newUsersThisWeek = User::where('created_at', '>=', Carbon::now()->subWeek())->count();
        $this->newFixerThisWeek = Fixer::where('created_at', '>=', Carbon::now()->subWeek())->count();
        $this->newActiveRequests = ServiceRequest::where('status', 'accepted')
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->count();

        $this->newServiceCompleted = ServiceRequest::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->count();

        $this->recentCustomers = User::where('status', 'Active')
            ->where('user_type', 'Customer')
            ->latest()
            ->take(5)
            ->get();

        $this->recentRequests = ServiceRequest::latest()
            ->take(5)
            ->get();

        $this->topRatedFixers = User::where('user_type', 'Fixer')
            ->withAvg([
                'receivedRatings as average_rating' => function ($query) {
                    $query->where('role', 'customer');
                }
            ], 'rating')
            ->orderByDesc('average_rating')
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
