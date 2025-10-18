@section('page-title', 'Location Options')

 <div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Locations</h4>

                        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                            data-bs-target="#createLocationModal">
                            + Create Location
                        </button>
                    </div>

                    <div class="modal fade" id="createLocationModal" tabindex="-1"
                        aria-labelledby="createLocationModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title " id="createLocationModalLabel">Create Location</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    @livewire('location-option.location-option-create')
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Status</th>
                                    <th style="width: 80px; min-width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($options as $index => $opt)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $opt->name }}</td>
                                        <td>{{ $opt->latitude }}</td>
                                        <td>{{ $opt->longitude }}</td>
                                        <td>
                                            <span class="badge rounded-pill {{ $opt->is_active ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                                {{ $opt->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-link font-size-16 shadow-none py-0 text-muted dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal"
                                                        data-bs-target="#editLocationModal{{ $opt->id }}">
                                                        Edit
                                                    </a>
                                                    <a class="dropdown-item" wire:click="toggle({{ $opt->id }})">
                                                        {{ $opt->is_active ? 'Deactivate' : 'Activate' }}
                                                    </a>
                                                    <a class="dropdown-item" wire:click="delete({{ $opt->id }})"
                                                        wire:confirm="Are you sure you want to delete this location?">
                                                        Delete
                                                    </a>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="editLocationModal{{ $opt->id }}" tabindex="-1"
                                        aria-labelledby="editLocationModalLabel{{ $opt->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editLocationModalLabel{{ $opt->id }}">Edit Location</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @livewire('location-option.location-option-edit', ['id' => $opt->id], key('location-edit-' . $opt->id))
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
