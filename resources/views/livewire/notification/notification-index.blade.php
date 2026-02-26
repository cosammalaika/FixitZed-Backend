@section('page-title', 'Notifications')

@php
    $authUser = auth()->user();
    $canBulkDeleteNotifications = $authUser && method_exists($authUser, 'hasRole') && $authUser->hasRole(['Super Admin', 'Admin']);
@endphp

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
                        @if ($canBulkDeleteNotifications)
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3"
                                id="notification-bulk-actions">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-danger btn-sm"
                                        id="bulk-delete-notifications-btn" disabled>
                                        Delete Selected
                                    </button>
                                    <span class="text-muted small" id="bulk-delete-notifications-count">Selected: 0</span>
                                </div>
                            </div>
                        @endif

                        <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    @if ($canBulkDeleteNotifications)
                                        <th style="min-width: 170px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="checkbox" id="select-notifications-page"
                                                    class="form-check-input" title="Select all on this page"
                                                    aria-label="Select all notifications on this page">
                                                <span class="small text-muted">Select all on this page</span>
                                            </div>
                                        </th>
                                    @endif
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
                                    <tr data-notification-id="{{ $note->id }}">
                                        @if ($canBulkDeleteNotifications)
                                            <td class="text-center align-middle">
                                                <input type="checkbox" class="form-check-input notification-row-select"
                                                    value="{{ $note->id }}"
                                                    aria-label="Select notification {{ $note->id }}">
                                            </td>
                                        @endif
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

@if ($canBulkDeleteNotifications)
    <script>
        (function () {
            function initNotificationBulkDelete() {
                var tableEl = document.getElementById('datatable-buttons');
                var buttonEl = document.getElementById('bulk-delete-notifications-btn');
                var countEl = document.getElementById('bulk-delete-notifications-count');
                var headerCheckboxEl = document.getElementById('select-notifications-page');

                if (!tableEl || !buttonEl || !countEl || !headerCheckboxEl) {
                    return;
                }

                if (tableEl.dataset.bulkDeleteInit === '1') {
                    return;
                }

                if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.dataTable) {
                    window.setTimeout(initNotificationBulkDelete, 150);
                    return;
                }

                if (!window.jQuery.fn.dataTable.isDataTable(tableEl)) {
                    window.setTimeout(initNotificationBulkDelete, 150);
                    return;
                }

                tableEl.dataset.bulkDeleteInit = '1';

                var $ = window.jQuery;
                var dataTable = $(tableEl).DataTable();
                var selectedIds = new Set();
                var bulkDeleteUrl = @json(route('admin.notifications.bulkDelete'));
                var csrfMeta = document.querySelector('meta[name=\"csrf-token\"]');
                var csrfToken = csrfMeta ? (csrfMeta.getAttribute('content') || '') : '';

                function notify(type, message) {
                    if (!message) return;
                    if (window.Swal && typeof window.Swal.fire === 'function') {
                        window.Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: type,
                            title: message,
                            timer: 3500,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });
                        return;
                    }
                    alert(message);
                }

                function visibleRowCheckboxes() {
                    var nodes = dataTable.rows({ page: 'current', search: 'applied' }).nodes();
                    return $(nodes).find('.notification-row-select');
                }

                function syncVisibleRowsFromSelection() {
                    visibleRowCheckboxes().each(function () {
                        var id = Number(this.value);
                        this.checked = selectedIds.has(id);
                    });
                }

                function updateHeaderCheckboxState() {
                    var checkboxes = visibleRowCheckboxes().toArray();
                    if (checkboxes.length === 0) {
                        headerCheckboxEl.checked = false;
                        headerCheckboxEl.indeterminate = false;
                        return;
                    }

                    var checkedCount = checkboxes.filter(function (cb) {
                        return cb.checked;
                    }).length;

                    headerCheckboxEl.checked = checkedCount > 0 && checkedCount === checkboxes.length;
                    headerCheckboxEl.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
                }

                function updateBulkActionState() {
                    var count = selectedIds.size;
                    countEl.textContent = 'Selected: ' + count;
                    buttonEl.disabled = count === 0 || buttonEl.dataset.loading === '1';
                    updateHeaderCheckboxState();
                }

                $(tableEl).on('change', '.notification-row-select', function () {
                    var id = Number(this.value);
                    if (Number.isNaN(id)) return;

                    if (this.checked) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }

                    updateBulkActionState();
                });

                headerCheckboxEl.addEventListener('change', function () {
                    var shouldCheck = headerCheckboxEl.checked;
                    visibleRowCheckboxes().each(function () {
                        var id = Number(this.value);
                        if (Number.isNaN(id)) return;

                        this.checked = shouldCheck;
                        if (shouldCheck) {
                            selectedIds.add(id);
                        } else {
                            selectedIds.delete(id);
                        }
                    });

                    updateBulkActionState();
                });

                $(tableEl).on('draw.dt', function () {
                    syncVisibleRowsFromSelection();
                    updateBulkActionState();
                });

                buttonEl.addEventListener('click', function () {
                    var ids = Array.from(selectedIds);
                    if (ids.length === 0) {
                        updateBulkActionState();
                        return;
                    }

                    if (!window.Swal || typeof window.Swal.fire !== 'function') {
                        if (!window.confirm('Delete ' + ids.length + ' notifications? This cannot be undone.')) {
                            return;
                        }
                        executeBulkDelete(ids);
                        return;
                    }

                    window.Swal.fire({
                        title: 'Delete ' + ids.length + ' notifications?',
                        text: 'This cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#F1592A',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete selected',
                        cancelButtonText: 'Cancel',
                    }).then(function (result) {
                        if (!result.isConfirmed) return;
                        executeBulkDelete(ids);
                    });
                });

                function executeBulkDelete(ids) {
                    buttonEl.dataset.loading = '1';
                    updateBulkActionState();

                    window.fetch(bulkDeleteUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ ids: ids }),
                    })
                    .then(async function (response) {
                        var payload = null;
                        try {
                            payload = await response.json();
                        } catch (e) {
                            payload = null;
                        }

                        if (!response.ok) {
                            var message = (payload && payload.message) ? payload.message : 'Bulk delete failed. Please try again.';
                            if (payload && payload.errors && payload.errors.ids && payload.errors.ids.length) {
                                message = payload.errors.ids[0];
                            }
                            throw new Error(message);
                        }

                        return payload || { deleted: 0, missing: 0, message: 'Bulk delete completed.' };
                    })
                    .then(function (payload) {
                        var attemptedIds = new Set(ids.map(function (id) { return Number(id); }));

                        dataTable.rows(function (idx, data, node) {
                            var checkbox = node ? node.querySelector('.notification-row-select') : null;
                            if (!checkbox) return false;
                            var rowId = Number(checkbox.value);
                            return attemptedIds.has(rowId);
                        }).remove();

                        dataTable.draw(false);
                        selectedIds.clear();
                        syncVisibleRowsFromSelection();
                        updateBulkActionState();
                        notify('success', payload.message || 'Notifications deleted successfully.');
                    })
                    .catch(function (error) {
                        notify('error', (error && error.message) ? error.message : 'Bulk delete failed. Please try again.');
                        updateBulkActionState();
                    })
                    .finally(function () {
                        delete buttonEl.dataset.loading;
                        updateBulkActionState();
                    });
                }

                syncVisibleRowsFromSelection();
                updateBulkActionState();
            }

            window.addEventListener('load', initNotificationBulkDelete);
            window.addEventListener('livewire:navigated', initNotificationBulkDelete);
        })();
    </script>
@endif
