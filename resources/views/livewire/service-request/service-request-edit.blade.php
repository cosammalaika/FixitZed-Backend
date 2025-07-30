<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            @if (session()->has('success'))
                <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit.prevent="update">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="customer_id">Customer</label>
                        <select wire:model="customer_id" class="form-control" required>
                            <option value="">-- Choose User --</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->first_name }}
                                    {{ $customer->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="fixer_id">Fixer</label>
                        <select wire:model="fixer_id" class="form-control">
                            <option value="">-- Select Fixer --</option>
                            @foreach ($fixers as $fixer)
                                <option value="{{ $fixer->id }}">{{ $fixer->user->first_name }}
                                    {{ $fixer->user->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="service_id">Service</label>
                        <select wire:model="service_id" class="form-control">
                            <option value="">-- Select Service --</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Scheduled At</label>
                        <input type="datetime-local" wire:model="scheduled_at" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="service_id">Status</label>
                        <select wire:model="status" class="form-control">
                            @foreach (['pending', 'accepted', 'completed', 'cancelled'] as $stat)
                                <option value="{{ $stat }}">{{ ucfirst($stat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Location</label>
                        <input wire:model="location" class="form-control"></input>
                    </div>
                </div><br>

                <button type="submit" class="btn btn-primary waves-effect waves-light">Create</button>
            </form>
        </div>
    </div>
</div>

{{-- <div>
    <h2>Edit Service Request</h2>

    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="update">
        <div class="mb-3">
            <label>Customer</label>
            <select wire:model="customer_id" class="form-control">
                <option value="">Select Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
            @error('customer_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Fixer</label>
            <select wire:model="fixer_id" class="form-control">
                <option value="">Select Fixer</option>
                @foreach($fixers as $fixer)
                    <option value="{{ $fixer->id }}">{{ $fixer->user->name }}</option>
                @endforeach
            </select>
            @error('fixer_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Service</label>
            <select wire:model="service_id" class="form-control">
                <option value="">Select Service</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
            </select>
            @error('service_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Scheduled At</label>
            <input type="datetime-local" wire:model="scheduled_at" class="form-control">
            @error('scheduled_at') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select wire:model="status" class="form-control">
                @foreach(['pending', 'accepted', 'completed', 'cancelled'] as $stat)
                    <option value="{{ $stat }}">{{ ucfirst($stat) }}</option>
                @endforeach
            </select>
            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Location</label>
            <textarea wire:model="location" class="form-control"></textarea>
            @error('location') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div> --}}
