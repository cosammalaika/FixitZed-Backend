@section('page-title', 'Ratings')

 <div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Ratings List</h4>
                    </div>

                    <div class="card-body">
                        @php
                            $resolveUserName = function ($user, $userId = null) {
                                if (! $user) {
                                    return $userId ? 'Deleted User' : 'Unknown User';
                                }

                                $name = trim((string) ($user->name ?? ''));
                                if ($name !== '') {
                                    return $name;
                                }

                                $fullName = trim((string) (($user->first_name ?? '') . ' ' . ($user->last_name ?? '')));

                                return $fullName !== '' ? $fullName : 'Unknown User';
                            };
                        @endphp
                        <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Rater</th>
                                    <th>Rated</th>
                                    <th>Service</th>
                                    <th>Role</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ratings as $index => $rating)
                                    <tr>
                                        <td>{{ $rating->id }}</td>
                                        <td>{{ $resolveUserName($rating->rater, $rating->rater_id) }}</td>
                                        <td>{{ $resolveUserName($rating->ratedUser, $rating->rated_user_id) }}</td>
                                        <td>
                                            @if ($rating->serviceRequest)
                                                #{{ $rating->serviceRequest->id }}
                                            @elseif ($rating->service_request_id)
                                                Deleted Request
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $rating->role ? ucfirst($rating->role) : 'N/A' }}</td>
                                        <td>{{ $rating->rating }}</td>
                                        <td>{{ filled($rating->comment) ? $rating->comment : 'N/A' }}</td>
                                    </tr>
                                @endforeach

                            </tbody>

                        </table>
                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div>
</div>
