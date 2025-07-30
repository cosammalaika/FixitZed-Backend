 <div class="page-content">
     <div class="container-fluid">
         <div class="row">
             <div class="col-12">
                 <div class="card">
                     <div class="card-header d-flex justify-content-between align-items-center">
                         <h4 class="card-title mb-0">Services Request List</h4>

                         <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                             data-bs-target="#createRoleModal">
                             + Create Service Request
                         </button>

                     </div>
                     @if (session('success'))
                         <div id="success-alert"
                             class="alert alert-success alert-top-border alert-dismissible fade show" role="alert">
                             <i class="mdi mdi-check-all me-3 align-middle text-success"></i><strong>Success</strong> -
                             {{ session('success') }}
                             <button type="button" class="btn-close" data-bs-dismiss="alert"
                                 aria-label="Close"></button>
                         </div>

                         <script>
                             setTimeout(() => {
                                 const alert = document.getElementById('success-alert');
                                 if (alert) {
                                     let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                                     bsAlert.close();
                                 }
                             }, 4000);
                         </script>
                     @endif

                     <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel"
                         aria-hidden="true">
                         <div class="modal-dialog modal-dialog-centered modal-lg">
                             <div class="modal-content">
                                 <div class="modal-header">
                                     <h5 class="modal-title " id="createRoleModalLabel">Create Service Request</h5>
                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                         aria-label="Close"></button>
                                 </div>

                                 <div class="modal-body">
                                     @livewire('service-request.service-request-create')
                                 </div>
                             </div>
                         </div>
                     </div>



                     <div class="card-body">
                         <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                             <thead>
                                 <tr>
                                     <th>#</th>
                                     <th>Customer Name</th>
                                     <th>Fixer Name</th>
                                     <th>Service Name</th>
                                     <th>Scheduled at</th>
                                     <th>Status</th>
                                     <th>Location</th>
                                     <th style="width: 80px; min-width: 80px;">Action</th>
                                 </tr>
                             </thead>


                             <tbody>
                                 @foreach ($serviceRequests as $index => $request)
                                     <tr>
                                         <td>{{ $index + 1 }}</td>
                                         <td>{{ $request->customer->first_name }} {{ $request->customer->last_name }}
                                         </td>
                                         <td>{{ $request->fixer->user->first_name }}
                                             {{ $request->fixer->user->last_name }}</td>
                                         <td>{{ $request->service->name ?? 'N/A' }}</td>

                                         <td>{{ $request->scheduled_at }}</td>
                                         <td><span
                                                 class="
                        @if ($request->status == 'accepted') badge rounded-pill badge-soft-success
                        @elseif($request->status == 'completed') badge rounded-pill badge-soft-primary
                        @elseif($request->status == 'cancelled') badge rounded-pill badge-soft-danger
                        @else badge rounded-pill badge-soft-warning @endif">
                                                 {{ $request->status }}
                                             </span></td>
                                         <td>{{ $request->location }}</td>
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
                                                         data-bs-target="#showRoleModal{{ $request->id }}">
                                                         Show
                                                     </a>


                                                     <a class="dropdown-item" href="javascript:void(0);"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#editRoleModal{{ $request->id }}">
                                                         Edit
                                                     </a>
                                                     </a>
                                                     <a class="dropdown-item" wire:click="delete({{ $request->id }})"
                                                         wire:confirm="Are you Sure you want to delete role"
                                                         variant="primary">
                                                         Delete
                                                     </a>
                                                     {{-- @can('show.requests') --}}
                                                     {{-- <li><a class="dropdown-item"
                                                 href="{{ route('requests.show', $request->id) }}">Show</a></li> --}}
                                                     {{-- @endcan --}}
                                                     {{-- @can('edit.requests') --}}
                                                     {{-- <li><a class="dropdown-item"
                                                 href="{{ route('requests.edit', $request->id) }}">Edit</a></li> --}}
                                                     {{-- @endcan --}}
                                                     {{-- @can('delete.requests') --}}
                                                     {{-- <li><a class="dropdown-item" wire:click="delete({{ $request->id }})"
                                                 wire:confirm="Are you Sure you want to delete request">Delete</a></li> --}}
                                                     {{-- @endcan --}}
                                                 </ul>
                                             </div>
                                         </td>
                                     </tr>
                                     <div class="modal fade" id="editRoleModal{{ $request->id }}" tabindex="-1"
                                         aria-labelledby="editRoleModalLabel{{ $request->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title"
                                                         id="editRoleModalLabel{{ $request->id }}">
                                                         Edit request</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('service-request.service-request-edit', ['id' => $request->id], key('request-edit-' . $request->id))
                                                 </div>

                                             </div>
                                         </div>
                                     </div>

                                     <!-- Show Modal for Each Role -->
                                     <div class="modal fade" id="showRoleModal{{ $request->id }}" tabindex="-1"
                                         aria-labelledby="showRoleModalLabel{{ $request->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title"
                                                         id="showRoleModalLabel{{ $request->id }}">
                                                         Show request</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('service-request.service-request-show', ['id' => $request->id], key('service-request-show-' . $request->id))
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
