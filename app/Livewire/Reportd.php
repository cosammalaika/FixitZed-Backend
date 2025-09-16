<?php

namespace App\Livewire;

use App\Models\Earning;
use App\Models\Fixer;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Component;

class Reportd extends Component
{
    public array $monthlyEarnings = [
        'labels' => [],
        'data' => [],
        'total' => 0.0,
        'current' => 0.0,
        'previous' => 0.0,
        'change' => null,
    ];
    public array $summary = [
        'totalRevenue' => 0.0,
        'averagePayout' => 0.0,
        'newCustomers' => 0,
        'pendingRequests' => 0,
    ];
    public array $newUsersSeries = [
        'labels' => [],
        'series' => [],
    ];
    public array $transactionOverview = [
        'labels' => [],
        'series' => [],
    ];
    public array $revenueSplit = [
        'labels' => [],
        'series' => [],
    ];
    public array $topFixers = [];
    public array $topServices = [];

    public function mount(): void
    {
        $this->monthlyEarnings = $this->buildMonthlyEarningsData();
        $this->summary = $this->buildSummary();
        $this->newUsersSeries = $this->buildNewUsersSeries();
        $this->transactionOverview = $this->buildTransactionOverview();
        $this->revenueSplit = $this->buildRevenueSplit();
        $this->topFixers = $this->buildTopFixers();
        $this->topServices = $this->buildTopServices();
    }

    public function render()
    {
        return view('livewire.reportd');
    }

    protected function buildMonthlyEarningsData(): array
    {
        $endOfRange = Carbon::now()->endOfMonth();
        $startOfRange = (clone $endOfRange)->subMonths(11)->startOfMonth();

        $totalsByMonth = Earning::whereBetween('created_at', [$startOfRange, $endOfRange])
            ->get()
            ->groupBy(fn (Earning $earning) => $earning->created_at->format('Y-m'))
            ->map(fn ($group) => round($group->sum('amount'), 2));

        $period = CarbonPeriod::create($startOfRange, '1 month', $endOfRange);

        $labels = [];
        $series = [];

        foreach ($period as $month) {
            $monthKey = $month->format('Y-m');
            $labels[] = $month->format('M');
            $series[] = round($totalsByMonth->get($monthKey, 0), 2);
        }

        $seriesCount = count($series);
        $currentMonth = $seriesCount ? $series[$seriesCount - 1] : 0.0;
        $previousMonth = $seriesCount > 1 ? $series[$seriesCount - 2] : 0.0;

        $change = null;
        if ($previousMonth > 0) {
            $change = round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2);
        }

        return [
            'labels' => $labels,
            'data' => $series,
            'total' => round(array_sum($series), 2),
            'current' => $currentMonth,
            'previous' => $previousMonth,
            'change' => $change,
        ];
    }

    protected function buildSummary(): array
    {
        $totalRevenue = (float) Earning::sum('amount');
        $averagePayout = (float) Earning::avg('amount');

        $pendingStatuses = ['pending', 'accepted', 'in_progress'];
        $completedRequests = ServiceRequest::where('status', 'completed')->count();
        $pendingRequests = ServiceRequest::whereIn('status', $pendingStatuses)->count();

        $startOfMonth = Carbon::now()->startOfMonth();
        $newCustomers = User::where('user_type', 'Customer')
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        return [
            'totalRevenue' => round($totalRevenue, 2),
            'averagePayout' => round($averagePayout, 2),
            'newCustomers' => $newCustomers,
            'pendingRequests' => $pendingRequests,
            'completedRequests' => $completedRequests,
        ];
    }

    protected function buildNewUsersSeries(): array
    {
        $endOfRange = Carbon::now()->endOfMonth();
        $startOfRange = (clone $endOfRange)->subMonths(11)->startOfMonth();

        $customerCounts = User::where('user_type', 'Customer')
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

    protected function buildRevenueSplit(): array
    {
        $topFixers = Earning::selectRaw('fixer_id, SUM(amount) as total')
            ->groupBy('fixer_id')
            ->with('fixer.user')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $labels = [];
        $series = [];

        foreach ($topFixers as $earning) {
            $user = $earning->fixer?->user;
            $name = trim(($user->first_name ?? 'Unknown') . ' ' . ($user->last_name ?? ''));
            $labels[] = $name ?: 'Unknown Fixer';
            $series[] = round((float) $earning->total, 2);
        }

        $displayedIds = $topFixers->pluck('fixer_id')->filter()->all();
        $otherTotal = empty($displayedIds)
            ? (float) Earning::sum('amount')
            : (float) Earning::whereNotIn('fixer_id', $displayedIds)->sum('amount');

        if ($otherTotal > 0) {
            $labels[] = 'Others';
            $series[] = round($otherTotal, 2);
        }

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }

    protected function buildTopFixers(): array
    {
        return Earning::selectRaw('fixer_id, SUM(amount) as total')
            ->groupBy('fixer_id')
            ->with('fixer.user')
            ->orderByDesc('total')
            ->take(10)
            ->get()
            ->map(function ($earning) {
                $user = $earning->fixer?->user;

                return [
                    'name' => trim(($user->first_name ?? 'Unknown') . ' ' . ($user->last_name ?? '')),
                    'total' => round((float) $earning->total, 2),
                ];
            })
            ->toArray();
    }

    protected function buildTopServices(): array
    {
        $services = ServiceRequest::selectRaw('service_id, COUNT(*) as total')
            ->whereNotNull('service_id')
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->with('service')
            ->take(10)
            ->get();

        $totalRequests = max($services->sum('total'), 1);

        return $services->map(function ($row) use ($totalRequests) {
            $service = $row->service;

            return [
                'name' => $service?->name ?? 'Unknown Service',
                'total' => (int) $row->total,
                'percentage' => round(($row->total / $totalRequests) * 100),
            ];
        })->toArray();
    }
}
