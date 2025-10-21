@section('page-title', 'User Logs')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">User Log List</h4>

                    </div>
                    <div class="card-body">
                        <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    {{-- <th>IP</th> --}}
                                    <th>Device</th>
                                    <th>Time</th>
                                </tr>
                            </thead>


                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ $log->user->first_name ?? 'Guest' }} {{ $log->user->last_name ?? 'Guest' }}</td>
                                        <td>{{ $log->action }}</td>
                                        <td>{{ $log->description }}</td>
                                        {{-- <td>{{ $log->ip_address }}</td> --}}
                                        <td>{{ \Str::limit($log->user_agent, 30) }}</td>
                                        <td>{{ $log->created_at->diffForHumans() }}</td>
                                    </tr>

                                @endforeach

                            </tbody>

                        </table>
                        {{-- {{ $logs->links() }} --}}
                    </div>
                </div>

                <!-- end cardaa -->
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div>
</div>
