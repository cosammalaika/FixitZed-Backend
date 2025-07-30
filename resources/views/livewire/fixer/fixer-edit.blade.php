<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            @if (session()->has('success'))
                <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit.prevent="submit" class="space-y-4">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="user_type">User Type</label>
                        <select wire:model="user_id" class="form-control" required>
                            <option value="">-- Choose User --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <div>
                            <label class="form-label">Status</label>
                            <select wire:model="status" class="form-control">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            @error('status')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
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
                    Edit Fixer
                </button>
            </form>
        </div>
    </div>
</div>
