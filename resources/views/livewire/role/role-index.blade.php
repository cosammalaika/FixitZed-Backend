@section('page-title', 'Roles')

 <div class="page-content">
     <div class="container-fluid">
         <div class="row">
             <div class="col-12">
                 <div class="card">
                     <div class="card-header d-flex justify-content-between align-items-center">
                         <h4 class="card-title mb-0">Roles</h4>
                         {{-- <a href="{{ route('roles.create') }}" class="btn btn-primary waves-effect waves-light">
                             + Create Role
                         </a> --}}
                         <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                             data-bs-target="#createRoleModal">
                             + Create Role
                         </button>

                     </div>
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
                                     @livewire('role.role-create')
                                 </div>
                             </div>
                         </div>
                     </div>



                     <div class="card-body">
                         <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                             <thead>
                                 <tr>
                                     <th>Id</th>
                                     <th>Name</th>
                                     <th>Permission</th>
                                     <th>Action</th>
                                 </tr>
                             </thead>


                             <tbody>
                                 @foreach ($roles as $role)
                                     <tr>
                                         <td>{{ $role->id }}</td>
                                         <td>{{ $role->name }}</td>

                                         <td class="py-2 px-4 border-b">
                                             @if ($role->permissions)
                                                 <div class="d-flex flex-wrap gap-1">
                                                     @foreach ($role->permissions as $permission)
                                                         <span
                                                             class="badge rounded-pill badge-soft-primary">{{ $permission->name }}</span>
                                                     @endforeach
                                                 </div>
                                             @endif
                                         </td>
                                         <td>
                                             <div class="btn-group">
                                                  <button
                                                    class="btn btn-link font-size-16 shadow-none py-0 text-muted dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                                </button>
                                                 <div class="dropdown-menu dropdownmenu-primary">
                                                     <!-- Button to trigger the modal -->
                                                     <a class="dropdown-item" href="javascript:void(0);"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#showRoleModal{{ $role->id }}">
                                                         Show
                                                     </a>


                                                     <a class="dropdown-item" href="javascript:void(0);"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#editRoleModal{{ $role->id }}">
                                                         Edit
                                                     </a>
                                                     </a>
                                                     <a class="dropdown-item text-danger" href="#"
                                                         data-confirm-event="deleteRole"
                                                         data-confirm-id="{{ $role->id }}"
                                                         data-confirm-title="Delete role?"
                                                         data-confirm-message="This role will be removed permanently."
                                                         data-confirm-button="Yes, delete it">
                                                         Delete
                                                     </a>
                                                 </div>
                                             </div>

                                         </td>

                                     </tr>
                                     <!-- Edit Modal -->
                                     <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1"
                                         aria-labelledby="editRoleModalLabel{{ $role->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title" id="editRoleModalLabel{{ $role->id }}">
                                                         Edit Role</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('role.role-edit', ['id' => $role->id], key('role-edit-' . $role->id))
                                                 </div>

                                             </div>
                                         </div>
                                     </div>

                                     <!-- Show Modal for Each Role -->
                                     <div class="modal fade" id="showRoleModal{{ $role->id }}" tabindex="-1"
                                         aria-labelledby="showRoleModalLabel{{ $role->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title"
                                                         id="showRoleModalLabel{{ $role->id }}">
                                                         Show Role</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('role.role-show', ['id' => $role->id], key('role-show-' . $role->id))
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
