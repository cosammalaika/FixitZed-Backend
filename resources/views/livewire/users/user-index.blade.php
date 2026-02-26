@section('page-title', 'Users')

@php
    $authUser = auth()->user();
    $isSuperAdmin = $authUser && method_exists($authUser, 'hasRole') && $authUser->hasRole('Super Admin');
    $canCreateUser = $authUser->can('create.users');
    $canShowUser = $authUser->can('show.users');
    $canEditUser = $authUser->can('edit.users');
    $canDeleteUser = $isSuperAdmin && $authUser->can('delete.users');
    $hasUserActions = $canShowUser || $canEditUser || $canDeleteUser;
@endphp

<div class="page-content" data-permission="view.users">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">User List</h4>

                        @if ($canCreateUser)
                            <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                                data-bs-target="#createRoleModal" data-permission="create.users">
                                + Add User
                            </button>
                        @endif
                    </div>

                    @if ($canCreateUser)
                        <div class="modal fade" id="createRoleModal" tabindex="-1"
                            aria-labelledby="createRoleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title " id="createRoleModalLabel"></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body" data-permission="create.users">
                                        @livewire('users.user-create')
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card-body">
                        <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Contact</th>
                                    <th scope="col">Province / Area</th>
                                    <th scope="col">Roles</th>
                                    <th scope="col">Status</th>
                                    @if ($hasUserActions)
                                        <th style="width: 80px; min-width: 80px;"
                                            data-permission-any="show.users|edit.users|delete.users">Action</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ trim($user->first_name . ' ' . $user->last_name) }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->contact_number }}</td>
                                        <td>
                                            @php
                                                $computedAddress = trim(($user->province ?? '') . ', ' . ($user->district ?? ''));
                                            @endphp
                                            {{ $computedAddress !== ',' ? trim($computedAddress, ' ,') : ($user->address ?? '-') }}
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @if ($user->roles)
                                                    @foreach ($user->roles as $role)
                                                        <span class="badge badge-soft-primary">{{ $role->name }}</span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($user->status === 'Active')
                                                <span class="badge rounded-pill badge-soft-success">Active</span>
                                            @elseif ($user->status === 'Inactive')
                                                <span class="badge rounded-pill badge-soft-danger">Inactive</span>
                                            @else
                                                <span class="badge rounded-pill badge-soft-warning">{{ $user->status }}</span>
                                            @endif
                                        </td>

                                        @if ($hasUserActions)
                                            <td data-permission-any="show.users|edit.users|delete.users">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                        id="dropdownMenuButton{{ $user->id }}" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    @php
                                                        $canDeleteThisUser = $canDeleteUser && $user->id !== $authUser->id && ! $user->hasRole('Super Admin');
                                                        $hasVisiblePrimaryActions = $canShowUser || $canEditUser;
                                                    @endphp
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $user->id }}">
                                                        @if ($canShowUser)
                                                            <li><a class="dropdown-item" data-bs-toggle="modal"
                                                                    data-permission="show.users"
                                                                    data-bs-target="#showRoleModal{{ $user->id }}">Show</a>
                                                            </li>
                                                        @endif

                                                        @if ($canEditUser)
                                                            <li><a class="dropdown-item" data-bs-toggle="modal"
                                                                    data-permission="edit.users"
                                                                    data-bs-target="#editRoleModal{{ $user->id }}">Edit</a>
                                                            </li>
                                                        @endif

                                                        @if ($canDeleteThisUser)
                                                            @if ($hasVisiblePrimaryActions)
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                            @endif
                                                            <li>
                                                                <form method="POST"
                                                                    action="{{ route('admin.users.destroy', $user) }}"
                                                                    onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="dropdown-item text-danger">Delete</button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>

                                    @if ($canEditUser)
                                        <div class="modal fade" id="editRoleModal{{ $user->id }}" tabindex="-1"
                                            aria-labelledby="editRoleModalLabel{{ $user->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editRoleModalLabel{{ $user->id }}">
                                                            Edit User</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body" data-permission="edit.users">
                                                        @livewire('users.user-edit', ['id' => $user->id], key('user-edit-' . $user->id))
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($canShowUser)
                                        <div class="modal fade" id="showRoleModal{{ $user->id }}" tabindex="-1"
                                            aria-labelledby="showRoleModalLabel{{ $user->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="showRoleModalLabel{{ $user->id }}">
                                                            Show User</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body" data-permission="show.users">
                                                        @livewire('users.user-show', ['id' => $user->id], key('user-show-' . $user->id))
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
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
