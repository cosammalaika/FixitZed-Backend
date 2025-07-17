<div class="container">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="submit">
                {{-- Name Field --}}
                <div class="mb-3">
                    <label for="formrow-firstname-input" class="form-label">Role name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                        id="formrow-firstname-input" wire:model="name" placeholder="Enter role name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Permissions --}}
                <div class="mb-4">
                    <label class="form-label d-block">Permissions</label>
                    <div class="row">
                        @foreach ($allPermissions as $permission)
                            <div class="col-md-4">
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" class="form-check-input" id="perm-{{ $permission->id }}"
                                        value="{{ $permission->name }}" wire:model="permissions">
                                    <label class="form-check-label" for="perm-{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @error('permissions')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div class="text-end">
                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
