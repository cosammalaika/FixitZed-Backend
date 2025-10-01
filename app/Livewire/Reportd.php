<?php

namespace App\Livewire;

use App\Models\Fixer;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

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

        $payments = Payment::where('status', 'paid')
            ->where(function ($query) use ($startOfRange, $endOfRange) {
                $query->whereBetween('paid_at', [$startOfRange, $endOfRange])
                    ->orWhere(function ($inner) use ($startOfRange, $endOfRange) {
                        $inner->whereNull('paid_at')
                            ->whereBetween('updated_at', [$startOfRange, $endOfRange]);
                    })
                    ->orWhere(function ($inner) use ($startOfRange, $endOfRange) {
                        $inner->whereNull('paid_at')
                            ->whereNull('updated_at')
                            ->whereBetween('created_at', [$startOfRange, $endOfRange]);
                    });
            })
            ->get();

        $totalsByMonth = $payments
            ->groupBy(function (Payment $payment) {
                $date = $payment->paid_at ?? $payment->updated_at ?? $payment->created_at;
                return Carbon::parse($date)->format('Y-m');
            })
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
        $revenueQuery = Payment::where('status', 'paid');
        $totalRevenue = (float) (clone $revenueQuery)->sum('amount');
        $averagePayout = (float) (clone $revenueQuery)->avg('amount');

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
        $baseQuery = Payment::where('payments.status', 'paid')
            ->join('service_requests', 'payments.service_request_id', '=', 'service_requests.id')
            ->join('fixers', 'service_requests.fixer_id', '=', 'fixers.id')
            ->join('users', 'fixers.user_id', '=', 'users.id')
            ->selectRaw('fixers.id as fixer_id, users.first_name, users.last_name, SUM(payments.amount) as total')
            ->groupBy('fixers.id', 'users.first_name', 'users.last_name');

        $topRows = (clone $baseQuery)
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $labels = [];
        $series = [];
        $displayedIds = [];

        foreach ($topRows as $row) {
          $name = trim(($row->first_name ?? 'Unknown') . ' ' . ($row->last_name ?? ''));
          $labels[] = $name !== '' ? $name : 'Unknown fixer';
          $series[] = round((float) $row->total, 2);
          $displayedIds[] = $row->fixer_id;
        }

        $totalForAssignedFixers = (clone $baseQuery)->sum('payments.amount');
        $topTotal = array_sum($series);
        $otherTotal = max(0, $totalForAssignedFixers - $topTotal);

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
        return Payment::where('payments.status', 'paid')
            ->join('service_requests', 'payments.service_request_id', '=', 'service_requests.id')
            ->join('fixers', 'service_requests.fixer_id', '=', 'fixers.id')
            ->join('users', 'fixers.user_id', '=', 'users.id')
            ->selectRaw('fixers.id as fixer_id, users.first_name, users.last_name, SUM(payments.amount) as total')
            ->groupBy('fixers.id', 'users.first_name', 'users.last_name')
            ->orderByDesc('total')
            ->take(10)
            ->get()
            ->map(function ($row) {
                $name = trim(($row->first_name ?? 'Unknown') . ' ' . ($row->last_name ?? ''));
                return [
                    'name' => $name !== '' ? $name : 'Unknown fixer',
                    'total' => round((float) $row->total, 2),
                ];
            })
            ->toArray();
    }

    protected function buildTopServices(): array
    {
        $services = Payment::where('payments.status', 'paid')
            ->join('service_requests', 'payments.service_request_id', '=', 'service_requests.id')
            ->join('services', 'service_requests.service_id', '=', 'services.id')
            ->selectRaw('services.id as service_id, services.name, COUNT(*) as bookings, SUM(payments.amount) as revenue')
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('revenue')
            ->take(10)
            ->get();

        $totalRevenue = max((float) $services->sum('revenue'), 1.0);

        return $services->map(function ($row) use ($totalRevenue) {
            $name = $row->name ?? 'Unknown service';
            $revenue = (float) $row->revenue;
            return [
                'name' => $name,
                'total' => (int) $row->bookings,
                'revenue' => round($revenue, 2),
                'percentage' => round(($revenue / $totalRevenue) * 100),
            ];
        })->toArray();
    }
}
