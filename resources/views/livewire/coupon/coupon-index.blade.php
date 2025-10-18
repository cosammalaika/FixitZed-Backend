@section('page-title', 'Coupons')

 <div class="page-content">
     <div class="container-fluid">
         <div class="row">
             <div class="col-12">
                 <div class="card">
                     <div class="card-header d-flex justify-content-between align-items-center">
                         <h4 class="card-title mb-0">Coupons List</h4>

                         <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                             data-bs-target="#createRoleModal">
                             + Add coupon
                         </button>

                     </div>
                    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel"
                         aria-hidden="true">
                         <div class="modal-dialog modal-dialog-centered modal-lg">
                             <div class="modal-content">
                                 <div class="modal-header">
                                     <h5 class="modal-title " id="createRoleModalLabel">Create coupon</h5>
                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                         aria-label="Close"></button>
                                 </div>

                                 <div class="modal-body">
                                     @livewire('coupon.coupon-create')
                                 </div>
                             </div>
                         </div>
                     </div>



                     <div class="card-body">
                         <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                             <thead>
                                 <tr>
                                     <th>#</th>
                                     <th>Code</th>
                                     <th>Title</th>
                                     <th>Discount Percent</th>
                                     <th>Discount Amount</th>
                                     <th>Valid From</th>
                                     <th>Valid To</th>
                                     <th>Usage Limit</th>
                                     <th>Used</th>
                                     <th style="width: 80px; min-width: 80px;">Action</th>
                                 </tr>
                             </thead>


                             <tbody>
                                 @foreach ($coupons as $index => $coupon)
                                     <tr>
                                         <td>{{ $index + 1 }}</td>
                                         <td>{{ $coupon->code }}</td>
                                         <td>{{ $coupon->title }}</td>
                                         <td>{{ $coupon->discount_percent }}%</td>
                                         <td>{{ $coupon->discount_amount }}</td>
                                         <td>{{ $coupon->valid_from }}</td>
                                         <td>{{ $coupon->valid_to }}</td>
                                         <td>{{ $coupon->usage_limit }}</td>
                                         <td>{{ $coupon->used_count }}</td>
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
                                                         data-bs-target="#showRoleModal{{ $coupon->id }}">
                                                         Show
                                                     </a>


                                                     <a class="dropdown-item" href="javascript:void(0);"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#editRoleModal{{ $coupon->id }}">
                                                         Edit
                                                     </a>
                                                     </a>
                                                     <a class="dropdown-item" wire:click="delete({{ $coupon->id }})"
                                                         wire:confirm="Are you Sure you want to delete role"
                                                         variant="primary">
                                                         Delete
                                                     </a>
                                                     {{-- @can('show.coupons') --}}
                                                     {{-- <li><a class="dropdown-item"
                                                 href="{{ route('coupons.show', $coupon->id) }}">Show</a></li> --}}
                                                     {{-- @endcan --}}
                                                     {{-- @can('edit.coupons') --}}
                                                     {{-- <li><a class="dropdown-item"
                                                 href="{{ route('coupons.edit', $coupon->id) }}">Edit</a></li> --}}
                                                     {{-- @endcan --}}
                                                     {{-- @can('delete.coupons') --}}
                                                     {{-- <li><a class="dropdown-item" wire:click="delete({{ $coupon->id }})"
                                                 wire:confirm="Are you Sure you want to delete coupon">Delete</a></li> --}}
                                                     {{-- @endcan --}}
                                                 </ul>
                                             </div>
                                         </td>
                                     </tr>
                                     <div class="modal fade" id="editRoleModal{{ $coupon->id }}" tabindex="-1"
                                         aria-labelledby="editRoleModalLabel{{ $coupon->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title"
                                                         id="editRoleModalLabel{{ $coupon->id }}">
                                                         Edit coupon</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('coupon.coupon-edit', ['id' => $coupon->id], key('coupon-edit-' . $coupon->id))
                                                 </div>

                                             </div>
                                         </div>
                                     </div>

                                     <!-- Show Modal for Each Role -->
                                     <div class="modal fade" id="showRoleModal{{ $coupon->id }}" tabindex="-1"
                                         aria-labelledby="showRoleModalLabel{{ $coupon->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title"
                                                         id="showRoleModalLabel{{ $coupon->id }}">
                                                         Show coupon</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('coupon.coupon-show', ['coupon' => $coupon], key('coupon-show-' . $coupon->id))
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
