
<div class="container">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="submit">
                <div class="mb-3">
                    <label for="roleName" class="form-label">Role Name</label>
                    <input type="text" class="form-control" id="roleName" value="{{ $role->name }}" readonly>
                </div>
                {{-- Permissions --}}
                <div class="mb-4">
                    <label class="form-label d-block">Permissions</label>
                    <div class="row">
                        @if ($role->permissions)
                            <div class="d-flex flex-wrap gap-1">
                                @foreach ($role->permissions as $permission)
                                    <span class="badge rounded-pill badge-soft-primary">{{ $permission->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        @error('permissions')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
            </form>
        </div>
    </div>
</div>
