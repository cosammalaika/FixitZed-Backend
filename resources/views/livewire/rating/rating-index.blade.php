<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Ratings List</h4>
                    </div>

                    <div class="card-body">
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
                                        <td>{{ $rating->rater->name ?? $rating->rater->first_name . ' ' . $rating->rater->last_name }}
                                        </td>
                                        <td>{{ $rating->ratedUser->name ?? $rating->ratedUser->first_name . ' ' . $rating->ratedUser->last_name }}
                                        </td>
                                        <td>#{{ $rating->serviceRequest->id }}</td>
                                        <td>{{ ucfirst($rating->role) }}</td>
                                        <td>{{ $rating->rating }}</td>
                                        <td>{{ $rating->comment ?? 'N/A' }}</td>
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
