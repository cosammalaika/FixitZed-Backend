<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">User List</h4>
                        {{-- <a href="{{ route('roles.create') }}" class="btn btn-primary waves-effect waves-light">
                             + Create Role
                         </a> --}}
                        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                            data-bs-target="#createRoleModal">
                            + Add User
                        </button>

                    </div>
                    @if (session('success'))
                        <div class="alert alert-success alert-top-border alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all me-3 align-middle text-success"></i><strong>Success</strong> -
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif
                    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title " id="createRoleModalLabel">Create Role</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    @livewire('users.user-create')
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="card-body">
                        <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">First Name</th>
                                    <th scope="col">Last Name</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Contact</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">Roles</th>
                                    <th scope="col">Status</th>
                                    <th style="width: 80px; min-width: 80px;">Action</th>
                                </tr>
                            </thead>


                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->first_name }}</td>
                                        <td>{{ $user->last_name }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->contact_number }}</td>
                                        <td>{{ $user->user_type }}</td>
                                        <td>{{ $user->address }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @if ($user->roles)
                                                    @foreach ($user->roles as $role)
                                                        <a href="#"
                                                            class="badge badge-soft-primary">{{ $role->name }}</a>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($user->status === 'Active')
                                                <span class="badge rounded-pill badge-soft-success">Active</span>
                                            @elseif($user->status === 'Inactive')
                                                <span class="badge rounded-pill badge-soft-danger">Inactive</span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-link font-size-16 shadow-none py-0 text-muted dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#showRoleModal{{ $user->id }}">
                                                        Show
                                                    </a>


                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editRoleModal{{ $user->id }}">
                                                        Edit
                                                    </a>
                                                    </a>
                                                    <a class="dropdown-item" wire:click="delete({{ $user->id }})"
                                                        wire:confirm="Are you Sure you want to delete role"
                                                        variant="primary">
                                                        Delete
                                                    </a>
                                                    {{-- @can('show.users') --}}
                                                    {{-- <li><a class="dropdown-item"
                                                 href="{{ route('users.show', $user->id) }}">Show</a></li> --}}
                                                    {{-- @endcan --}}
                                                    {{-- @can('edit.users') --}}
                                                    {{-- <li><a class="dropdown-item"
                                                 href="{{ route('users.edit', $user->id) }}">Edit</a></li> --}}
                                                    {{-- @endcan --}}
                                                    {{-- @can('delete.users') --}}
                                                    {{-- <li><a class="dropdown-item" wire:click="delete({{ $user->id }})"
                                                 wire:confirm="Are you Sure you want to delete user">Delete</a></li> --}}
                                                    {{-- @endcan --}}
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="editRoleModal{{ $user->id }}" tabindex="-1"
                                        aria-labelledby="editRoleModalLabel{{ $user->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">

                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editRoleModalLabel{{ $user->id }}">
                                                        Edit Role</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    @livewire('users.user-edit', ['id' => $user->id], key('user-edit-' . $user->id))
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <!-- Show Modal for Each Role -->
                                    <div class="modal fade" id="showRoleModal{{ $user->id }}" tabindex="-1"
                                        aria-labelledby="showRoleModalLabel{{ $user->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">

                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="showRoleModalLabel{{ $user->id }}">
                                                        Show Role</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    @livewire('users.user-show', ['id' => $user->id], key('user-show-' . $user->id))
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </tbody>

                        </table>
                    </div>
                </div>

                <!-- end cardaa -->
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div>
</div>
