@section('page-title', 'Notifications')

 <div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Notifications List</h4>

                        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                            data-bs-target="#createNotificationModal">
                            + Add Notification
                        </button>
                    </div>

                    <!-- Create Notification Modal -->
                    <div class="modal fade" id="createNotificationModal" tabindex="-1" aria-labelledby="createNotificationModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="createNotificationModalLabel">Create Notification</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    @livewire('notification.notification-create')
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User / Group</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th style="width: 80px; min-width: 80px;">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($notifications as $note)
                                    <tr>
                                        <td>{{ $note->id }}</td>
                                        <td>
                                            @if ($note->recipient_type === 'Individual')
                                                {{ optional($note->user)->name ?? 'N/A' }}
                                            @else
                                                {{ $note->recipient_type }}
                                            @endif
                                        </td>
                                        <td>{{ $note->title }}</td>
                                        <td>
                                            @if ($note->read)
                                                <span class="badge bg-success">Read</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Unread</span>
                                            @endif
                                        </td>
                                        <td>{{ $note->created_at->diffForHumans() }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-link font-size-16 shadow-none py-0 text-muted dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0);"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#showNotificationModal{{ $note->id }}">
                                                            Show
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0);"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editNotificationModal{{ $note->id }}">
                                                            Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            data-confirm-event="deleteNotification"
                                                            data-confirm-id="{{ $note->id }}"
                                                            data-confirm-title="Delete notification?"
                                                            data-confirm-message="This notification will be removed permanently."
                                                            data-confirm-button="Yes, delete it">
                                                            Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editNotificationModal{{ $note->id }}" tabindex="-1"
                                        aria-labelledby="editNotificationModalLabel{{ $note->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">

                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editNotificationModalLabel{{ $note->id }}">
                                                        Edit Notification
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    @livewire('notification.notification-edit', ['id' => $note->id], key('notification-edit-' . $note->id))
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <!-- Show Modal -->
                                    <div class="modal fade" id="showNotificationModal{{ $note->id }}" tabindex="-1"
                                        aria-labelledby="showNotificationModalLabel{{ $note->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">

                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="showNotificationModalLabel{{ $note->id }}">
                                                        Show Notification
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    @livewire('notification.notification-show', ['id' => $note->id], key('notification-show-' . $note->id))
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
                <!-- end card -->
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div>
</div>
