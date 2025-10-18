@section('page-title', 'Fixers')

 <div class="page-content">
     <div class="container-fluid">
         <div class="row">
             <div class="col-12">
                 <div class="card">
                     <div class="card-header d-flex justify-content-between align-items-center">
                         <h4 class="card-title mb-0">fixers List</h4>

                         <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                             data-bs-target="#createRoleModal">
                             + Add fixer
                         </button>

                     </div>
                    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel"
                        aria-hidden="true">
                         <div class="modal-dialog modal-dialog-centered modal-lg">
                             <div class="modal-content">
                                 <div class="modal-header">
                                     <h5 class="modal-title " id="createRoleModalLabel">Create fixer</h5>
                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                         aria-label="Close"></button>
                                 </div>

                                 <div class="modal-body">
                                     @livewire('fixer.fixer-create')
                                 </div>
                             </div>
                         </div>
                     </div>



                     <div class="card-body">
                         <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                             <thead>
                                 <tr>
                                     <th>#</th>
                                     <th>Name</th>
                                     <th>Email</th>
                                    <th>Status</th>
                                    <th>Subscription</th>
                                    <th>Coins</th>
                                     <th>Rating</th>
                                     <th>Bio</th>
                                     <th>Services</th>
                                     <th>Joined</th>
                                     <th style="width: 80px; min-width: 80px;">Action</th>
                                 </tr>
                             </thead>


                             <tbody>
                                 @foreach ($fixers as $index => $fixer)
                                     <tr>
                                         <td>{{ $index + 1 }}</td>
                                         <td>{{ $fixer->user->first_name }} {{ $fixer->user->last_name }}</td>
                                         <td>{{ $fixer->user->email }}</td>
                                         <td class="py-2 px-4 capitalize">
                                             <span
                                                 class="
                        @if ($fixer->status == 'approved') badge rounded-pill badge-soft-success
                        @elseif($fixer->status == 'rejected') badge rounded-pill badge-soft-danger
                        @else badge rounded-pill badge-soft-warning @endif">
                                                 {{ $fixer->status }}
                                             </span>
                                         </td>
                                         <td class="py-2 px-4">
                                             @php($sub = optional($fixer->wallet)->subscription_status ?? 'pending')
                                             <span class="badge rounded-pill {{ $sub === 'approved' ? 'badge-soft-success' : 'badge-soft-warning' }}">
                                                 {{ ucfirst($sub) }}
                                             </span>
                                         </td>
                                         <td class="py-2 px-4">{{ optional($fixer->wallet)->coin_balance ?? 0 }}</td>
                                         <td>{{ number_format($fixer->rating_avg, 1) }}/5</td>
                                         <td>{{ Str::limit($fixer->bio, 40) ?? 'N/A' }}</td>
                                         <td>
                                             @if ($fixer->services->isNotEmpty())
                                                 @foreach ($fixer->services as $service)
                                                     <span class="badge bg-secondary me-1">{{ $service->name }}</span>
                                                 @endforeach
                                             @else
                                                 <span class="text-muted">No services</span>
                                             @endif
                                         </td>

                                         <td>{{ $fixer->created_at->format('M d, Y') }}</td>
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
                                                         data-bs-target="#showRoleModal{{ $fixer->id }}">
                                                         Show
                                                     </a>


                                                     <a class="dropdown-item" href="javascript:void(0);"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#editRoleModal{{ $fixer->id }}">
                                                         Edit
                                                     </a>
                                                     </a>
                                                     <a class="dropdown-item text-danger" href="#"
                                                         data-confirm-event="deleteFixer"
                                                         data-confirm-id="{{ $fixer->id }}"
                                                         data-confirm-title="Delete fixer?"
                                                         data-confirm-message="This fixer will be removed permanently."
                                                         data-confirm-button="Yes, delete it">
                                                         Delete
                                                     </a>

                                                 </ul>
                                             </div>
                                         </td>
                                     </tr>
                                     <div class="modal fade" id="editRoleModal{{ $fixer->id }}" tabindex="-1"
                                         aria-labelledby="editRoleModalLabel{{ $fixer->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title"
                                                         id="editRoleModalLabel{{ $fixer->id }}">
                                                         Edit fixer</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('fixer.fixer-edit', ['id' => $fixer->id], key('fixer-edit-' . $fixer->id))
                                                 </div>

                                             </div>
                                         </div>
                                     </div>

                                     <!-- Show Modal for Each Role -->
                                     <div class="modal fade" id="showRoleModal{{ $fixer->id }}" tabindex="-1"
                                         aria-labelledby="showRoleModalLabel{{ $fixer->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title"
                                                         id="showRoleModalLabel{{ $fixer->id }}">
                                                         Show fixer</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('fixer.fixer-show', ['id' => $fixer->id], key('fixer-show-' . $fixer->id))
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
