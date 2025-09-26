@section('page-title', 'Categories')

 <div class="page-content">
     <div class="container-fluid">
         <div class="row">
             <div class="col-12">
                 <div class="card">
                     <div class="card-header d-flex justify-content-between align-items-center">
                         <h4 class="card-title mb-0">Category List</h4>

                         <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                             data-bs-target="#createRoleModal">
                             + Create Category
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
                                     <h5 class="modal-title " id="createRoleModalLabel">Create Category</h5>
                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                         aria-label="Close"></button>
                                 </div>

                                 <div class="modal-body">
                                     @livewire('category.category-create')
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
                                     <th>Description</th>
                                     <th style="width: 80px; min-width: 80px;">Action</th>
                                 </tr>
                             </thead>


                             <tbody>
                                 @foreach ($categories as $index => $category)
                                     <tr>
                                         <td>{{ $index + 1 }}</td>
                                         <td>{{ $category->name }}</td>
                                         <td>{{ $category->description }}</td>
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
                                                         data-bs-target="#showRoleModal{{ $category->id }}">
                                                         Show
                                                     </a>


                                                     <a class="dropdown-item" href="javascript:void(0);"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#editRoleModal{{ $category->id }}">
                                                         Edit
                                                     </a>
                                                     </a>
                                                     <a class="dropdown-item" wire:click="delete({{ $category->id }})"
                                                         wire:confirm="Are you Sure you want to delete role"
                                                         variant="primary">
                                                         Delete
                                                     </a>
                                                     {{-- @can('show.categorys') --}}
                                                     {{-- <li><a class="dropdown-item"
                                                 href="{{ route('categorys.show', $category->id) }}">Show</a></li> --}}
                                                     {{-- @endcan --}}
                                                     {{-- @can('edit.categorys') --}}
                                                     {{-- <li><a class="dropdown-item"
                                                 href="{{ route('categorys.edit', $category->id) }}">Edit</a></li> --}}
                                                     {{-- @endcan --}}
                                                     {{-- @can('delete.categorys') --}}
                                                     {{-- <li><a class="dropdown-item" wire:click="delete({{ $category->id }})"
                                                 wire:confirm="Are you Sure you want to delete category">Delete</a></li> --}}
                                                     {{-- @endcan --}}
                                                 </ul>
                                             </div>
                                         </td>
                                     </tr>
                                     <div class="modal fade" id="editRoleModal{{ $category->id }}" tabindex="-1"
                                         aria-labelledby="editRoleModalLabel{{ $category->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title"
                                                         id="editRoleModalLabel{{ $category->id }}">
                                                         Edit category</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('category.category-edit', ['id' => $category->id], key('category-edit-' . $category->id))
                                                 </div>

                                             </div>
                                         </div>
                                     </div>

                                     <!-- Show Modal for Each Role -->
                                     <div class="modal fade" id="showRoleModal{{ $category->id }}" tabindex="-1"
                                         aria-labelledby="showRoleModalLabel{{ $category->id }}" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered modal-lg">
                                             <div class="modal-content">

                                                 <div class="modal-header">
                                                     <h5 class="modal-title"
                                                         id="showRoleModalLabel{{ $category->id }}">
                                                         Show category</h5>
                                                     <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                         aria-label="Close"></button>
                                                 </div>

                                                 <div class="modal-body">
                                                     @livewire('category.category-show', ['id' => $category->id], key('category-show-' . $category->id))
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
