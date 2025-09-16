@section('page-title', 'Reports')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Reports</h4>
                    </div>
                </div>
            </div>

            @php
                $earnings = $monthlyEarnings ?? ['labels' => [], 'data' => [], 'total' => 0, 'current' => 0, 'previous' => 0, 'change' => null];
                $change = $earnings['change'];
                $changeClass = $change === null ? 'text-muted' : ($change >= 0 ? 'text-success' : 'text-danger');
                $summary = $summary ?? [];
            @endphp

            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <p class="text-muted mb-2">Total Revenue</p>
                            <h4 class="fw-semibold">ZMW {{ number_format($summary['totalRevenue'] ?? 0, 2) }}</h4>
                            <span class="badge bg-soft-success text-success">Lifetime</span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <p class="text-muted mb-2">Average Payout</p>
                            <h4 class="fw-semibold">ZMW {{ number_format($summary['averagePayout'] ?? 0, 2) }}</h4>
                            <span class="badge bg-soft-primary text-primary">Per earning</span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <p class="text-muted mb-2">New Customers ({{ now()->format('M') }})</p>
                            <h4 class="fw-semibold">{{ $summary['newCustomers'] ?? 0 }}</h4>
                            <span class="badge bg-soft-info text-info">This month</span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <p class="text-muted mb-2">Pending Requests</p>
                            <h4 class="fw-semibold">{{ $summary['pendingRequests'] ?? 0 }}</h4>
                            <span class="badge bg-soft-warning text-warning">Active pipeline</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2 mb-0">Monthly Earnings</h5>
                                <span class="badge bg-soft-primary text-primary">Last 12 months</span>
                            </div>

                            <div class="row align-items-center">
                                <div class="col-xl-4">
                                    <div class="pe-xl-4 mb-4 mb-xl-0 text-muted">
                                        <p class="mb-2">Current month</p>
                                        <h4 class="mb-3 fw-semibold">ZMW {{ number_format($earnings['current'], 2) }}</h4>
                                        <p class="mb-2">12 month total:</p>
                                        <p class="mb-2 fw-semibold">ZMW {{ number_format($earnings['total'], 2) }}</p>
                                        <p class="mb-4">Previous month: ZMW {{ number_format($earnings['previous'], 2) }}</p>
                                        <p class="mb-0 {{ $changeClass }}">
                                            @if ($change === null)
                                                No previous month data
                                            @else
                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 1) }}% vs last month
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-xl-8">
                                    @if (count($earnings['data']))
                                        <div id="monthly-earnings-chart"
                                            data-colors='["#f1592a"]'
                                            data-series='@json($earnings['data'])'
                                            data-labels='@json($earnings['labels'])'
                                            data-currency="ZMW "
                                            class="apex-charts"
                                            style="min-height: 320px;">
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-5">
                                            No earnings recorded for the selected period.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2 mb-0">New Users Trend</h5>
                            </div>
                            @if (!empty($newUsersSeries['series']))
                                <div id="report-new-users-chart"
                                    data-colors='["#f1592a", "#fbbf24"]'
                                    data-labels='@json($newUsersSeries['labels'])'
                                    data-series='@json($newUsersSeries['series'])'
                                    class="apex-charts"
                                    style="min-height: 320px;">
                                </div>
                            @else
                                <div class="text-center text-muted py-5">No user data available.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2 mb-0">Service Request Status</h5>
                                <span class="text-muted small">Completed vs Pending</span>
                            </div>
                            @if (!empty($transactionOverview['series']))
                                <div id="report-transaction-chart"
                                    data-colors='["#f1592a", "#fbbf24"]'
                                    data-labels='@json($transactionOverview['labels'])'
                                    data-series='@json($transactionOverview['series'])'
                                    class="apex-charts"
                                    style="min-height: 320px;">
                                </div>
                            @else
                                <div class="text-center text-muted py-5">No transaction data available.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2 mb-0">Revenue Share</h5>
                            </div>
                            @if (!empty($revenueSplit['series']))
                                <div id="revenue-split-chart"
                                    data-colors='["#f1592a", "#f97316", "#fb923c", "#fdba74", "#fef3c7"]'
                                    data-labels='@json($revenueSplit['labels'])'
                                    data-series='@json($revenueSplit['series'])'
                                    class="apex-charts"
                                    style="min-height: 320px;">
                                </div>
                            @else
                                <div class="text-center text-muted py-5">No earnings data available.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2 mb-0">Top Earning Fixers</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <thead class="text-muted">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Fixer</th>
                                            <th scope="col" class="text-end">Revenue (ZMW)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($topFixers as $index => $fixer)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $fixer['name'] }}</td>
                                                <td class="text-end">{{ number_format($fixer['total'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No earnings available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2 mb-0">Top Requested Services</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <thead class="text-muted">
                                        <tr>
                                            <th scope="col">Service</th>
                                            <th scope="col" class="text-center">Requests</th>
                                            <th scope="col" class="text-end">Share</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($topServices as $service)
                                            <tr>
                                                <td>{{ $service['name'] }}</td>
                                                <td class="text-center">{{ $service['total'] }}</td>
                                                <td class="text-end">{{ $service['percentage'] }}%</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No service data available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @include('includes.Footer')
</div>
