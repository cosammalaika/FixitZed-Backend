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
                                    <div id="mini-chart1" data-colors='["#f1592a"]' class="apex-charts mb-2">
                                    </div>
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
                                    <div id="mini-chart2" data-colors='["#f1592a"]' class="apex-charts mb-2">
                                    </div>
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
                                    <div id="mini-chart3" data-colors='["#f1592a"]' class="apex-charts mb-2">
                                    </div>
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
                                    <div id="mini-chart4" data-colors='["#f1592a"]' class="apex-charts mb-2">
                                    </div>
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
                    <!-- card -->
                    <div class="card">
                        <!-- inside .card-body -->
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2">Monthly Earnings</h5>
                                <!-- ... buttons ... -->
                            </div>

                            <div id="mini-chart4" data-colors='["#f1592a"]' class="apex-charts mb-2">
                            </div>
                            <!-- Your existing rows/cards -->
                            <div class="row align-items-center">
                                <!-- existing col-xl-4 content here -->
                            </div>
                        </div>

                    </div>
                    <!-- end col -->
                </div>
                <!-- end row-->

                <div class="col-xl-4">
                    <!-- card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2">Top 5 Rated Fixer</h5>
                            </div>
                            <table class="table table-borderless dt-responsive nowrap w-100">
                                <thead>
                                    <tr class="text-primary">
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>


                                <tbody>
                                    @forelse ($topRatedFixers as $index => $fixer)
                                        <tr>
                                            <td><strong>{{ $index + 1  }}</strong></td>
                                            <td> <strong>{{ $fixer->first_name  }} {{ $fixer->last_name  }}</strong><br>
                                            </td>
                                            <td>{{ number_format($fixer->average_rating, 1) }}/5</td>
                                        </tr>
                                    @empty
                                        <p class="text-muted">No ratings available.</p>
                                    @endforelse
                                </tbody>
                            </table>

                                <!-- Your existing rows/cards -->
                                <div class="row align-items-center">
                                    <!-- existing col-xl-4 content here -->
                                </div>
                        </div>

                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>
                <!-- end col -->
            </div>
            <!-- end row-->



        </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->


    @include('includes.Footer')

</div>
