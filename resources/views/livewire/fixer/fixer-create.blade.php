@section('page-title', 'Create Fixer')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">

            <form wire:submit.prevent="submit" class="space-y-4">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="user_id">User</label>
                        <select wire:model="user_id" class="form-control" data-trigger required>
                            <option value="">-- Choose User --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div>
                            <label class="form-label">Status</label>
                            <select wire:model="status" class="form-control" data-trigger>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            @error('status')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between">
                            <label for="selected_services" class="form-label fw-semibold mb-0">Services Skilled In</label>
                            <small class="text-muted">Selected ({{ count($selected_services ?? []) }})</small>
                        </div>
                        <div class="mt-2">
                            @php
                                $serviceMap = ($services ?? collect())->keyBy(fn ($s) => (string) $s->id);
                            @endphp
                            <div class="border rounded p-3 bg-white" style="min-height: 120px;">
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse ($selected_services as $sid)
                                        @php
                                            $service = $serviceMap->get((string) $sid);
                                            $name = $service->name ?? 'Unknown';
                                        @endphp
                                        <span class="badge rounded-pill d-inline-flex align-items-center" style="background:#ff7f32; color:#fff; padding:4px 10px; font-size:0.78rem; gap:6px;">
                                            <span>{{ $name }}</span>
                                            <button type="button" class="btn btn-sm btn-link text-white p-0 m-0" style="line-height:1; font-size:0.9rem;" wire:click.prevent="removeService('{{ $sid }}')">
                                                &times;
                                            </button>
                                        </span>
                                    @empty
                                        <span class="text-muted">No services selected.</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="mt-3 position-relative">
                                <button type="button" class="form-control text-start d-flex justify-content-between align-items-center" wire:click="toggleServiceDropdown">
                                    <span>Select services</span>
                                    <span class="badge bg-light text-muted">{{ $totalServices }} total</span>
                                </button>
                                @if ($showServiceDropdown)
                                    <div class="border rounded shadow-sm bg-white mt-1 p-2" style="max-height: 260px; overflow-y: auto; position: absolute; width: 100%; z-index: 1050;">
                                        <input type="text" class="form-control form-control-sm mb-2" placeholder="Search services…" wire:model.debounce.250ms="serviceSearch">
                                        <div class="d-flex flex-column gap-1">
                                            @forelse ($services as $service)
                                                <button type="button"
                                                    class="btn btn-sm text-start {{ in_array((string) $service->id, $selected_services ?? []) ? 'btn-outline-primary' : 'btn-light' }}"
                                                    wire:click="toggleService('{{ $service->id }}')">
                                                    {{ $service->name }}
                                                </button>
                                            @empty
                                                <span class="text-muted small px-2 py-1">No services found.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @error('selected_services')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

        </div>

        <div class="row">

            <div class="col-lg-12">
                <div class="mb-3">
                    <label for="progresspill-address-input">Bio</label>
                    <textarea id="progresspill-address-input" wire:model="bio" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary waves-effect waves-light">
            Create Fixer
        </button>
        </form>
    </div>
</div>
</div>
