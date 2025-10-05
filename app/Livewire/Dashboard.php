<?php

namespace App\Livewire;

use App\Models\Fixer;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalUsers, $totalFixers, $activeRequests, $serviceCompleted, $newUsersThisWeek, $newFixerThisWeek, $newActiveRequests, $newServiceCompleted, $recentCustomers, $recentRequests;
    public $topRatedFixers;
    public array $sparklineSeries = [];
    public array $newUsersSeries = [
        'labels' => [],
        'series' => [],
    ];
    public array $transactionOverview = [
        'labels' => [],
        'series' => [],
    ];
    public array $topActiveUsers = [];
    public array $topRequestedServices = [];

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

        $this->recentCustomers = User::role('Customer')
            ->where('status', 'Active')
            ->latest()
            ->take(5)
            ->get();

        $this->recentRequests = ServiceRequest::latest()
            ->take(5)
            ->get();

        $this->topRatedFixers = User::role('Fixer')
            ->withAvg([
                'receivedRatings as average_rating' => function ($query) {
                    $query->where('role', 'customer');
                }
            ], 'rating')
            ->orderByDesc('average_rating')
            ->take(10)
            ->get();

        $this->newUsersSeries = $this->buildNewUsersSeries();
        $this->transactionOverview = $this->buildTransactionOverview();
        $this->topActiveUsers = $this->buildTopActiveUsers();
        $this->topRequestedServices = $this->buildTopRequestedServices();
        $this->sparklineSeries = $this->buildSparklineData();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }

    protected function buildNewUsersSeries(): array
    {
        $endOfRange = Carbon::now()->endOfMonth();
        $startOfRange = (clone $endOfRange)->subMonths(11)->startOfMonth();

        $customerCounts = User::role('Customer')
            ->whereBetween('created_at', [$startOfRange, $endOfRange])
            ->get()
            ->groupBy(fn (User $user) => $user->created_at->format('Y-m'))
            ->map->count();

        $fixerCounts = Fixer::whereBetween('created_at', [$startOfRange, $endOfRange])
            ->get()
            ->groupBy(fn (Fixer $fixer) => $fixer->created_at->format('Y-m'))
            ->map->count();

        $period = CarbonPeriod::create($startOfRange, '1 month', $endOfRange);

        $labels = [];
        $customerSeries = [];
        $fixerSeries = [];

        foreach ($period as $month) {
            $monthKey = $month->format('Y-m');
            $labels[] = $month->format('M');
            $customerSeries[] = (int) ($customerCounts[$monthKey] ?? 0);
            $fixerSeries[] = (int) ($fixerCounts[$monthKey] ?? 0);
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Customer', 'data' => $customerSeries],
                ['name' => 'Fixer', 'data' => $fixerSeries],
            ],
        ];
    }

    protected function buildTransactionOverview(): array
    {
        $endOfRange = Carbon::now()->endOfMonth();
        $startOfRange = (clone $endOfRange)->subMonths(11)->startOfMonth();

        $completed = ServiceRequest::where('status', 'completed')
            ->whereBetween('created_at', [$startOfRange, $endOfRange])
            ->get()
            ->groupBy(fn (ServiceRequest $request) => $request->created_at->format('Y-m'))
            ->map->count();

        $pending = ServiceRequest::whereBetween('created_at', [$startOfRange, $endOfRange])
            ->whereIn('status', ['pending', 'accepted', 'in_progress'])
            ->get()
            ->groupBy(fn (ServiceRequest $request) => $request->created_at->format('Y-m'))
            ->map->count();

        $period = CarbonPeriod::create($startOfRange, '1 month', $endOfRange);

        $labels = [];
        $completedSeries = [];
        $pendingSeries = [];

        foreach ($period as $month) {
            $monthKey = $month->format('Y-m');
            $labels[] = $month->format('M');
            $completedSeries[] = (int) ($completed[$monthKey] ?? 0);
            $pendingSeries[] = (int) ($pending[$monthKey] ?? 0);
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Completed', 'data' => $completedSeries],
                ['name' => 'Pending', 'data' => $pendingSeries],
            ],
        ];
    }

    protected function buildTopActiveUsers(): array
    {
        return ServiceRequest::selectRaw('fixer_id, COUNT(*) as total')
            ->whereNotNull('fixer_id')
            ->groupBy('fixer_id')
            ->orderByDesc('total')
            ->with('fixer.user')
            ->take(10)
            ->get()
            ->map(function ($request) {
                $user = $request->fixer?->user;

                return [
                    'name' => trim(($user->first_name ?? 'Unknown') . ' ' . ($user->last_name ?? '')),
                    'role' => $user?->primary_role ?? 'Fixer',
                    'total' => (int) $request->total,
                ];
            })
            ->toArray();
    }

    protected function buildTopRequestedServices(): array
    {
        $services = ServiceRequest::selectRaw('service_id, COUNT(*) as total')
            ->whereNotNull('service_id')
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->with('service')
            ->take(6)
            ->get();

        $totalRequests = max($services->sum('total'), 1);

        return $services->map(function ($row) use ($totalRequests) {
            $service = optional($row->service);

            return [
                'name' => $service->name ?? 'Unknown Service',
                'total' => (int) $row->total,
                'percentage' => round(($row->total / $totalRequests) * 100),
            ];
        })->toArray();
    }

    protected function buildSparklineData(): array
    {
        $end = Carbon::now()->endOfDay();
        $start = (clone $end)->subDays(6)->startOfDay();

        return [
            'users' => $this->buildDailySeriesFor(User::class, $start, $end, function ($query) {
                $query->where('status', 'Active');
            }),
            'fixers' => $this->buildDailySeriesFor(Fixer::class, $start, $end, function ($query) {
                $query->where('status', 'approved');
            }),
            'activeRequests' => $this->buildDailySeriesFor(ServiceRequest::class, $start, $end, function ($query) {
                $query->where('status', 'accepted');
            }),
            'completedRequests' => $this->buildDailySeriesFor(ServiceRequest::class, $start, $end, function ($query) {
                $query->where('status', 'completed');
            }),
        ];
    }

    protected function buildDailySeriesFor(string $modelClass, Carbon $start, Carbon $end, callable $callback): array
    {
        $query = $modelClass::query();

        $callback($query);

        $records = $query
            ->whereBetween('created_at', [$start, $end])
            ->pluck('created_at')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->countBy();

        $period = CarbonPeriod::create($start, '1 day', $end);

        $series = [];

        foreach ($period as $day) {
            $series[] = (int) ($records[$day->format('Y-m-d')] ?? 0);
        }

        return $series;
    }

}
