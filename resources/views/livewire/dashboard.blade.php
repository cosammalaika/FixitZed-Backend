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
                                        <span class="counter-value" data-target="{{ $totalUsers }}">0</span>
                                    </h4>
                                </div>

                                <div class="col-6">
                                    <div id="mini-chart1" data-colors='["#f1592a"]' class="apex-charts mb-2">
                                    </div>
                                </div>
                            </div>
                            <div class="text-nowrap">
                                <span class="badge bg-soft-success text-success">+{{ $newUsersThisWeek ?? 0}} Users</span>
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
                                        <span class="counter-value" data-target="{{ $totalFixers }}">0</span>
                                    </h4>
                                </div>
                                <div class="col-4">
                                    <div id="mini-chart2" data-colors='["#f1592a"]' class="apex-charts mb-2">
                                    </div>
                                </div>
                            </div>
                            <div class="text-nowrap">
                                <span class="badge bg-soft-success text-success">
                                    +{{ $newFixerThisWeek ?? 0 }} Fixers </span>
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
                                        <span class="counter-value" data-target="{{ $activeRequests }}">0</span>
                                    </h4>
                                </div>
                                <div class="col-6">
                                    <div id="mini-chart3" data-colors='["#f1592a"]' class="apex-charts mb-2">
                                    </div>
                                </div>
                            </div>
                            <div class="text-nowrap">
                                <span class="badge bg-soft-success text-success">+{{ $newActiveRequests ?? 0 }} Active Requests</span>
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
                                    <span class="text-muted mb-3 lh-1 d-block text-truncate">Completed Services Request</span>
                                    <h4 class="mb-3">
                                        <span class="counter-value" data-target="{{ $serviceCompleted }}">0</span>
                                    </h4>
                                </div>
                                <div class="col-5">
                                    <div id="mini-chart4" data-colors='["#f1592a"]' class="apex-charts mb-2">
                                    </div>
                                </div>
                            </div>
                            <div class="text-nowrap">
                                <span class="badge bg-soft-success text-success">+{{ $newServiceCompleted ?? 0 }} Services
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
                        <!-- card body -->
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2">Market Overview</h5>
                                <div class="ms-auto">
                                    <div>
                                        <button type="button" class="btn btn-soft-primary btn-sm">
                                            ALL
                                        </button>
                                        <button type="button" class="btn btn-soft-secondary btn-sm">
                                            1M
                                        </button>
                                        <button type="button" class="btn btn-soft-secondary btn-sm">
                                            6M
                                        </button>
                                        <button type="button" class="btn btn-soft-secondary btn-sm active">
                                            1Y
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center">
                               
                                <div class="col-xl-4">
                                    <div class="p-4">
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm m-auto">
                                                    <span
                                                        class="avatar-title rounded-circle bg-soft-light text-dark font-size-16">
                                                        1
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <span class="font-size-16">Coinmarketcap</span>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <span
                                                        class="badge rounded-pill badge-soft-success font-size-12 fw-medium">+2.5%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm m-auto">
                                                    <span
                                                        class="avatar-title rounded-circle bg-soft-light text-dark font-size-16">
                                                        2
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <span class="font-size-16">Binance</span>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <span
                                                        class="badge rounded-pill badge-soft-success font-size-12 fw-medium">+8.3%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm m-auto">
                                                    <span
                                                        class="avatar-title rounded-circle bg-soft-light text-dark font-size-16">
                                                        3
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <span class="font-size-16">Coinbase</span>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <span
                                                        class="badge rounded-pill badge-soft-danger font-size-12 fw-medium">-3.6%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm m-auto">
                                                    <span
                                                        class="avatar-title rounded-circle bg-soft-light text-dark font-size-16">
                                                        4
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <span class="font-size-16">Yobit</span>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <span
                                                        class="badge rounded-pill badge-soft-success font-size-12 fw-medium">+7.1%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm m-auto">
                                                    <span
                                                        class="avatar-title rounded-circle bg-soft-light text-dark font-size-16">
                                                        5
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <span class="font-size-16">Bitfinex</span>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <span
                                                        class="badge rounded-pill badge-soft-danger font-size-12 fw-medium">-0.9%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4 pt-2">
                                            <a href="{{ asset('#') }}" class="btn btn-primary w-100">See
                                                All Balances <i class="mdi mdi-arrow-right ms-1"></i></a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row-->

                <div class="col-xl-4">
                    <!-- card -->
                    <div class="card">
                        <!-- card body -->
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center mb-4">
                                <h5 class="card-title me-2">Sales by Locations</h5>
                                <div class="ms-auto">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-reset" href="{{ asset('#') }}"
                                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            <span class="text-muted font-size-12">Sort By:</span> <span
                                                class="fw-medium">World<i
                                                    class="mdi mdi-chevron-down ms-1"></i></span>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-end"
                                            aria-labelledby="dropdownMenuButton1">
                                            <a class="dropdown-item" href="{{ asset('#') }}">USA</a>
                                            <a class="dropdown-item" href="{{ asset('#') }}">Russia</a>
                                            <a class="dropdown-item" href="{{ asset('#') }}">Australia</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="sales-by-locations" data-colors='["#f1592a"]' style="height: 250px">
                            </div>

                            <div class="px-2 py-2">
                                <p class="mb-1">USA <span class="float-end">75%</span></p>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar"
                                        style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="75">
                                    </div>
                                </div>

                                <p class="mt-3 mb-1">Russia <span class="float-end">55%</span></p>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar"
                                        style="width: 55%" aria-valuenow="55" aria-valuemin="0" aria-valuemax="55">
                                    </div>
                                </div>

                                <p class="mt-3 mb-1">Australia <span class="float-end">85%</span></p>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar"
                                        style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="85">
                                    </div>
                                </div>
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
