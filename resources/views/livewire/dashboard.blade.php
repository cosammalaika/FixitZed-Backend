@section('page-title', 'Dashboard')

<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Dashboard</h4>

                        {{-- <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a
                                                href="{{ asset('javascript: void(0);') }}">Layouts</a></li>
                                        <li class="breadcrumb-item active">Dashboard</li>
                                    </ol>
                                </div> --}}

                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Active
                                        Users</span>
                                    <h4 class="mb-3">
                                        <span class="counter-value" data-target="{{ $totalUsers  }}">0</span>
                                    </h4>
                                </div>

                                <div class="col-6">
                                    <div id="mini-chart1"
                                        data-colors='["#f1592a"]'
                                        data-series='@json($sparklineSeries['users'] ?? [])'
                                        class="apex-charts mb-2"></div>
                                </div>
                            </div>
                            <div class="text-nowrap">
                                <span class="badge bg-soft-success text-success">+{{ $newUsersThisWeek ?? 0  }}
                                    Users</span>
                                <span class="ms-1 text-muted font-size-13">Since last week</span>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Active
                                        Fixers</span>
                                    <h4 class="mb-3">
                                        <span class="counter-value" data-target="{{ $totalFixers  }}">0</span>
                                    </h4>
                                </div>
                                <div class="col-4">
                                    <div id="mini-chart2"
                                        data-colors='["#f1592a"]'
                                        data-series='@json($sparklineSeries['fixers'] ?? [])'
                                        class="apex-charts mb-2"></div>
                                </div>
                            </div>
                            <div class="text-nowrap">
                                <span class="badge bg-soft-success text-success">
                                    +{{ $newFixerThisWeek ?? 0  }} Fixers </span>
                                <span class="ms-1 text-muted font-size-13">Since last week</span>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col-->

                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Active
                                        Requests</span>
                                    <h4 class="mb-3">
                                        <span class="counter-value" data-target="{{ $activeRequests  }}">0</span>
                                    </h4>
                                </div>
                                <div class="col-6">
                                    <div id="mini-chart3"
                                        data-colors='["#f1592a"]'
                                        data-series='@json($sparklineSeries['activeRequests'] ?? [])'
                                        class="apex-charts mb-2"></div>
                                </div>
                            </div>
                            <div class="text-nowrap">
                                <span class="badge bg-soft-success text-success">+{{ $newActiveRequests ?? 0  }} Active
                                    Requests</span>
                                <span class="ms-1 text-muted font-size-13">Since last week</span>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-h-100">
                        <!-- card body -->
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-7">
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Completed Services
                                        Request</span>
                                    <h4 class="mb-3">
                                        <span class="counter-value" data-target="{{ $serviceCompleted  }}">0</span>
                                    </h4>
                                </div>
                                <div class="col-5">
                                    <div id="mini-chart4"
                                        data-colors='["#f1592a"]'
                                        data-series='@json($sparklineSeries['completedRequests'] ?? [])'
                                        class="apex-charts mb-2"></div>
                                </div>
                            </div>
                            <div class="text-nowrap">
                                <span class="badge bg-soft-success text-success">+{{ $newServiceCompleted ?? 0  }}
                                    Services
                                    Completed</span>
                                <span class="ms-1 text-muted font-size-13">Since last week</span>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->
            </div><!-- end row-->


            <div class="row">
                <div class="col-xl-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                                <h5 class="card-title mb-0">New Users</h5>
                                <div class="text-muted small">Last 12 months</div>
                            </div>
                            <div id="new-users-chart"
                                data-colors='["#f1592a", "#fbbf24"]'
                                data-labels='@json($newUsersSeries['labels'])'
                                data-series='@json($newUsersSeries['series'])'
                                class="apex-charts"
                                style="min-height: 320px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2 mb-0">Top Rated Fixers</h5>
                                <span class="text-muted small">Based on customer reviews</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <thead class="text-muted">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Name</th>
                                            <th scope="col" class="text-end">Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($topRatedFixers as $index => $fixer)
                                            <tr>
                                                <td class="fw-semibold">{{ $index + 1 }}</td>
                                                <td>{{ $fixer->first_name }} {{ $fixer->last_name }}</td>
                                                <td class="text-end">
                                                    <span class="badge bg-soft-warning text-warning">
                                                        {{ number_format($fixer->average_rating, 1) }}/5
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No ratings available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2 mb-0">Top 10 Active Users</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <thead class="text-muted">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Role</th>
                                            <th scope="col" class="text-end">Jobs</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($topActiveUsers as $index => $activeUser)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $activeUser['name'] }}</td>
                                                <td><span class="badge bg-soft-primary text-primary">{{ $activeUser['role'] }}</span></td>
                                                <td class="text-end fw-semibold">{{ $activeUser['total'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No activity recorded yet.</td>
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
                                        @forelse ($topRequestedServices as $service)
                                            <tr>
                                                <td>{{ $service['name'] }}</td>
                                                <td class="text-center">{{ $service['total'] }}</td>
                                                <td class="text-end">{{ $service['percentage'] }}%</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No service requests yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->



        </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->


    @include('includes.Footer')

</div>
