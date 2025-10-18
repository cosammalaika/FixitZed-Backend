@section('page-title', 'Earnings')

 <div class="page-content">
     <div class="container-fluid">
         <div class="row">
             <div class="col-12">
                 <div class="card">
                     <div class="card-header d-flex justify-content-between align-items-center">
                         <h4 class="card-title mb-0">Earnings List</h4>

                     </div>



                    <div class="card-body">
                         <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                             <thead>
                                 <tr>
                                     <th>#</th>
                                     <th>Fixer</th>
                                     <th>Paid Requests</th>
                                     <th>Total Amount Earned</th>
                                     <th style="width: 80px; min-width: 80px;">Action</th>
                                 </tr>
                             </thead>


                             <tbody>
                                 @foreach ($earnings as $earning)
                                     <tr>
                                         <td>{{ $earning->id }}</td>
                                         <td>{{ $earning->fixer->user->first_name }} {{ $earning->fixer->user->last_name }}</td>
                                         <td>{{ (int) $earning->service_count }}</td>
                                         <td>ZMW {{ number_format($earning->amount ?? 0, 2) }}</td>
                                         <td>
                                             <div class="dropdown">
                                                 <button
                                                     class="btn btn-link font-size-16 shadow-none py-0 text-muted dropdown-toggle"
                                                     type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                     <i class="bx bx-dots-horizontal-rounded"></i>
                                                 </button>

                                                 <ul class="dropdown-menu dropdown-menu-end">
                                                     {{-- <a class="dropdown-item" href="javascript:void(0);"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#showRoleModal{{ $earning->id }}">
                                                         Show
                                                     </a>


                                                     <a class="dropdown-item" href="javascript:void(0);"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#editRoleModal{{ $earning->id }}">
                                                         Edit
                                                     </a> --}}
                                                     <a class="dropdown-item" wire:click="delete({{ $earning->id }})"
                                                         wire:confirm="Are you Sure you want to delete role"
                                                         variant="primary">
                                                         Delete
                                                     </a>
                                                     {{-- @can('show.earnings') --}}
                                                     {{-- <li><a class="dropdown-item"
                                                 href="{{ route('earnings.show', $earning->id) }}">Show</a></li> --}}
                                                     {{-- @endcan --}}
                                                     {{-- @can('edit.earnings') --}}
                                                     {{-- <li><a class="dropdown-item"
                                                 href="{{ route('earnings.edit', $earning->id) }}">Edit</a></li> --}}
                                                     {{-- @endcan --}}
                                                     {{-- @can('delete.earnings') --}}
                                                     {{-- <li><a class="dropdown-item" wire:click="delete({{ $earning->id }})"
                                                 wire:confirm="Are you Sure you want to delete earning">Delete</a></li> --}}
                                                     {{-- @endcan --}}
                                                 </ul>
                                             </div>
                                         </td>
                                     </tr>
                                     <div class="modal fade" id="editRoleModal{{ $earning->id }}" tabindex="-1"
                                         aria-labelledby="editRoleModalLabel{{ $earning->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title"
                                                         id="editRoleModalLabel{{ $earning->id }}">
                                                         Edit earning</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('earning.earning-edit', ['id' => $earning->id], key('earning-edit-' . $earning->id))
                                                 </div>

                                             </div>
                                         </div>
                                     </div>

                                     <!-- Show Modal for Each Role -->
                                     <div class="modal fade" id="showRoleModal{{ $earning->id }}" tabindex="-1"
                                         aria-labelledby="showRoleModalLabel{{ $earning->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title"
                                                         id="showRoleModalLabel{{ $earning->id }}">
                                                         Show earning</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('earning.earning-show', ['id' => $earning->id], key('earning-show-' . $earning->id))
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
