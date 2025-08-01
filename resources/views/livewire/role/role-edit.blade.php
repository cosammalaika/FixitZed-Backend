{{-- <div>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Edit Role') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Edit new role') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    <div>


        <a href="{{ route('role.index') }}"
            class="cursor-pointer px-3 py-2 text-xs font-medium text-black bg-green-700 rounded-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300">
            Back
        </a>
        <div wire:submit="submit" class="w-50">
            <form class="mt-6 space-y-6">
                <flux:input wire:model="name" label="Name" placeholder="Name" />
                <flux:checkbox.group wire:model="permissions" label="Permissions">
                    @foreach ($allPermissions as $permission)
                        <flux:checkbox label="{{ $permission->name }}" value="{{ $permission->name }}" checked />
                    @endforeach
                </flux:checkbox.group>
                <flux:button type="submit" variant="primary">Submit</flux:button>
            </form>
        </div>
    </div>

</div> --}}
<div class="container">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="submit">
                {{-- Name Field --}}
                <div class="mb-3">
                    <label for="formrow-firstname-input" class="form-label">Role name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                    class="form-control" id="roleName"
                         wire:model="name" placeholder="Enter role name">
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
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
