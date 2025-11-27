@php use Illuminate\Support\Facades\Storage; @endphp
@section('page-title', 'Fixer Applications')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Pending Fixer Applications</h4>
                    </div>

                    <div class="card-body" wire:poll.15s>
                        <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Profile Photo</th>
                                    <th>NRC</th>
                                    <th>Work Photos</th>
                                    <th>Services</th>
                                    <th>Applied</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($applications as $index => $fixer)
                                    @php
                                        $user = $fixer->user;
                                        $workPhotos = (array) ($user?->work_photos ?? []);
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $user?->first_name }} {{ $user?->last_name }}</td>
                                        <td>{{ $user?->email }}</td>
                                        <td>
                                            @if ($user?->profile_photo_path)
                                                <a href="{{ Storage::disk('public')->url($user->profile_photo_path) }}" target="_blank">View</a>
                                            @else
                                                <span class="text-muted">Missing</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                @if ($user?->nrc_front_path)
                                                    <a href="{{ Storage::disk('public')->url($user->nrc_front_path) }}" target="_blank">Front</a>
                                                @else
                                                    <span class="text-muted">Front missing</span>
                                                @endif
                                                @if ($user?->nrc_back_path)
                                                    <a href="{{ Storage::disk('public')->url($user->nrc_back_path) }}" target="_blank">Back</a>
                                                @else
                                                    <span class="text-muted">Back missing</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ count($workPhotos) }}/3</td>
                                        <td>
                                            @if ($fixer->services->isNotEmpty())
                                                @foreach ($fixer->services as $service)
                                                    <span class="badge bg-secondary me-1">{{ $service->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">None</span>
                                            @endif
                                        </td>
                                        <td>{{ $fixer->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <button class="btn btn-success btn-sm" wire:click="approve({{ $fixer->id }})">Approve</button>
                                                <button class="btn btn-outline-danger btn-sm" wire:click="reject({{ $fixer->id }})">Reject</button>
                                                <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal"
                                                    data-bs-target="#showRoleModal{{ $fixer->id }}">
                                                    View Docs
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="showRoleModal{{ $fixer->id }}" tabindex="-1"
                                        aria-labelledby="showRoleModalLabel{{ $fixer->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="showRoleModalLabel{{ $fixer->id }}">
                                                        Fixer Application
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @livewire('fixer.fixer-show', ['id' => $fixer->id], key('fixer-show-' . $fixer->id . '-applications'))
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No pending applications.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
